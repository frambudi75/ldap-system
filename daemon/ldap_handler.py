"""
LDAP Protocol Handler for Custom LDAP Server.
Handles decoding of incoming LDAP requests, mapping to MySQL data,
and generating compliant LDAP responses (RFC 4511 / RFC 2251).
"""

import time
import logging
from pyasn1.codec.ber import decoder, encoder
from pyasn1_modules import rfc2251

logger = logging.getLogger("LDAP_HANDLER")

# LDAP Standard Result Codes
RESULT_SUCCESS = 0
RESULT_OPERATIONS_ERROR = 1
RESULT_PROTOCOL_ERROR = 2
RESULT_TIMELIMIT_EXCEEDED = 3
RESULT_SIZELIMIT_EXCEEDED = 4
RESULT_COMPARE_FALSE = 5
RESULT_COMPARE_TRUE = 6
RESULT_AUTH_METHOD_NOT_SUPPORTED = 7
RESULT_NO_SUCH_OBJECT = 32
RESULT_INAPPROPRIATE_AUTH = 48
RESULT_INVALID_CREDENTIALS = 49
RESULT_INSUFFICIENT_ACCESS_RIGHTS = 50
RESULT_BUSY = 51
RESULT_UNAVAILABLE = 52
RESULT_UNWILLING_TO_PERFORM = 53
RESULT_OTHER = 80


class LdapHandler:
    def __init__(self, db_manager):
        self.db = db_manager

    def process_data(self, raw_bytes, client_ip="127.0.0.1"):
        """
        Decode raw BER bytes into LDAPMessage and generate response messages.
        Returns a list of encoded response byte chunks.
        """
        responses = []
        if not raw_bytes:
            return responses

        try:
            ldap_msg, rest = decoder.decode(raw_bytes, asn1Spec=rfc2251.LDAPMessage())
        except Exception as e:
            logger.error(f"Failed to decode LDAP ASN.1 BER message: {e}")
            return responses

        msg_id = int(ldap_msg.getComponentByName("messageID"))
        protocol_op = ldap_msg.getComponentByName("protocolOp")
        op_name = protocol_op.getName()

        logger.info(f"[{client_ip}] Received LDAP Operation: {op_name} (MsgID: {msg_id})")

        start_time = time.time()

        if op_name == "bindRequest":
            resp_bytes = self._handle_bind(protocol_op.getComponent(), msg_id, client_ip, start_time)
            if resp_bytes:
                responses.append(resp_bytes)

        elif op_name == "searchRequest":
            resp_list = self._handle_search(protocol_op.getComponent(), msg_id, client_ip, start_time)
            responses.extend(resp_list)

        elif op_name == "unbindRequest":
            logger.info(f"[{client_ip}] Client requested unbind.")
            self.db.log_audit(client_ip, "UNBIND", None, None, "SUCCESS", None, int((time.time() - start_time) * 1000))
            return responses

        elif op_name == "abandonRequest":
            logger.info(f"[{client_ip}] Client requested abandon.")

        else:
            logger.warning(f"[{client_ip}] Unsupported operation: {op_name}")

        return responses

    def _create_bind_response(self, msg_id, result_code, matched_dn="", error_msg=""):
        """Build an encoded BindResponse LDAPMessage."""
        resp_msg = rfc2251.LDAPMessage()
        resp_msg.setComponentByName("messageID", msg_id)

        bind_resp = rfc2251.BindResponse()
        bind_resp.setComponentByName("resultCode", result_code)
        bind_resp.setComponentByName("matchedDN", matched_dn)
        bind_resp.setComponentByName("errorMessage", error_msg)

        op = rfc2251.LDAPMessage()["protocolOp"]
        op.setComponentByName("bindResponse", bind_resp)
        resp_msg.setComponentByName("protocolOp", op)

        return encoder.encode(resp_msg)

    def _handle_bind(self, bind_req, msg_id, client_ip, start_time):
        """Handle LDAP Simple Bind request."""
        version = int(bind_req.getComponentByName("version"))
        bind_name = str(bind_req.getComponentByName("name")).strip()
        auth_choice = bind_req.getComponentByName("authentication")
        auth_type = auth_choice.getName()

        if auth_type != "simple":
            logger.warning(f"[{client_ip}] Unsupported auth method: {auth_type}")
            return self._create_bind_response(
                msg_id, RESULT_AUTH_METHOD_NOT_SUPPORTED, bind_name, "Only simple bind authentication is supported"
            )

        password = str(auth_choice.getComponent())

        # 1. Anonymous Bind
        if not bind_name and not password:
            if self.db.is_anonymous_allowed():
                logger.info(f"[{client_ip}] Anonymous bind successful.")
                self.db.log_audit(client_ip, "BIND_ANONYMOUS", "", None, "SUCCESS", None, int((time.time() - start_time) * 1000))
                return self._create_bind_response(msg_id, RESULT_SUCCESS)
            else:
                logger.warning(f"[{client_ip}] Anonymous bind rejected (disabled).")
                self.db.log_audit(client_ip, "BIND_ANONYMOUS", "", None, "FAILED", "Anonymous bind is disabled", int((time.time() - start_time) * 1000))
                return self._create_bind_response(msg_id, RESULT_INAPPROPRIATE_AUTH, "", "Anonymous bind not allowed")

        # 2. Admin / Root Service Bind
        admin_dn = self.db.get_admin_bind_dn()
        if bind_name.lower() == admin_dn.lower() or bind_name.lower() in ("cn=admin", "admin"):
            if self.db.verify_admin_password(password):
                logger.info(f"[{client_ip}] Admin bind successful as '{bind_name}'.")
                self.db.log_audit(client_ip, "BIND_ADMIN", bind_name, None, "SUCCESS", None, int((time.time() - start_time) * 1000))
                return self._create_bind_response(msg_id, RESULT_SUCCESS)
            else:
                logger.warning(f"[{client_ip}] Admin bind failed: Invalid password.")
                self.db.log_audit(client_ip, "BIND_ADMIN", bind_name, None, "FAILED", "Invalid admin password", int((time.time() - start_time) * 1000))
                return self._create_bind_response(msg_id, RESULT_INVALID_CREDENTIALS, bind_name, "Invalid credentials")

        # 3. User Authentication Bind
        user = self.db.get_user_by_username_or_dn(bind_name)
        if not user:
            logger.warning(f"[{client_ip}] User bind failed: User '{bind_name}' not found.")
            self.db.log_audit(client_ip, "BIND_USER", bind_name, None, "FAILED", "User not found", int((time.time() - start_time) * 1000))
            return self._create_bind_response(msg_id, RESULT_INVALID_CREDENTIALS, bind_name, "Invalid credentials")

        if user.get("status") != "active":
            logger.warning(f"[{client_ip}] User bind failed: Account '{user.get('username')}' is disabled/suspended.")
            self.db.log_audit(client_ip, "BIND_USER", bind_name, None, "FAILED", "Account is disabled", int((time.time() - start_time) * 1000))
            return self._create_bind_response(msg_id, RESULT_INVALID_CREDENTIALS, bind_name, "Account is disabled")

        if self.db.verify_password(password, user.get("password_hash")):
            logger.info(f"[{client_ip}] User bind successful for '{user.get('username')}'.")
            self.db.log_audit(client_ip, "BIND_USER", bind_name, None, "SUCCESS", None, int((time.time() - start_time) * 1000))
            return self._create_bind_response(msg_id, RESULT_SUCCESS)
        else:
            logger.warning(f"[{client_ip}] User bind failed for '{user.get('username')}': Password mismatch.")
            self.db.log_audit(client_ip, "BIND_USER", bind_name, None, "FAILED", "Invalid password", int((time.time() - start_time) * 1000))
            return self._create_bind_response(msg_id, RESULT_INVALID_CREDENTIALS, bind_name, "Invalid credentials")

    def _handle_search(self, search_req, msg_id, client_ip, start_time):
        """Handle LDAP Search request and return list of encoded entries + done message."""
        base_obj = str(search_req.getComponentByName("baseObject")).lower().strip()
        scope = int(search_req.getComponentByName("scope"))
        filter_obj = search_req.getComponentByName("filter")
        attr_seq = search_req.getComponentByName("attributes")

        requested_attrs = [str(attr_seq.getComponentByPosition(i)).lower() for i in range(len(attr_seq))]
        filter_desc = self._filter_to_string(filter_obj)

        logger.info(f"[{client_ip}] Search Request: Base='{base_obj}', Filter='{filter_desc}', Scope={scope}")

        responses = []
        base_dn = self.db.get_base_dn()
        users = self.db.get_all_active_users()
        groups = self.db.get_all_groups()

        matched_entries = []

        # 1. Match Users
        for u in users:
            user_entry = self._build_user_ldap_entry(u, base_dn)
            if self._matches_filter(filter_obj, user_entry):
                matched_entries.append(user_entry)

        # 2. Match Groups
        for g in groups:
            group_entry = self._build_group_ldap_entry(g, base_dn)
            if self._matches_filter(filter_obj, group_entry):
                matched_entries.append(group_entry)

        # 3. Base Object (Root DSE / Base DN)
        if not base_obj or base_obj == base_dn:
            root_entry = {
                "dn": base_dn,
                "objectclass": ["top", "domain", "organization"],
                "dc": [base_dn.split(",")[0].replace("dc=", "")],
                "o": [self.db.get_setting("org_name", "Internal Directory Service")]
            }
            if self._matches_filter(filter_obj, root_entry) and scope in (0, 2):
                matched_entries.insert(0, root_entry)

        # Encode SearchResultEntry for each match
        for entry in matched_entries:
            entry_bytes = self._create_search_entry_response(msg_id, entry, requested_attrs)
            responses.append(entry_bytes)

        # Encode SearchResultDone
        done_bytes = self._create_search_done_response(msg_id, RESULT_SUCCESS)
        responses.append(done_bytes)

        duration = int((time.time() - start_time) * 1000)
        logger.info(f"[{client_ip}] Search completed: {len(matched_entries)} entries returned ({duration}ms).")
        self.db.log_audit(client_ip, "SEARCH", base_obj, filter_desc, "SUCCESS", f"{len(matched_entries)} entries returned", duration)

        return responses

    def _build_user_ldap_entry(self, user, base_dn):
        """Convert MySQL user row to standard LDAP attribute dictionary."""
        username = user.get("username", "")
        ou = user.get("ou", "users")
        dn = f"uid={username},ou={ou},{base_dn}"

        group_names = user.get("group_names") or ""
        member_of = []
        if group_names:
            for gname in group_names.split(","):
                gname = gname.strip()
                if gname:
                    member_of.append(f"cn={gname},ou=groups,{base_dn}")

        entry = {
            "dn": dn,
            "objectclass": ["top", "person", "organizationalPerson", "inetOrgPerson", "posixAccount"],
            "uid": [username],
            "samaccountname": [username],
            "cn": [user.get("full_name") or username],
            "displayname": [user.get("full_name") or username],
            "givenname": [user.get("first_name") or username],
            "sn": [user.get("last_name") or user.get("full_name") or username],
            "mail": [user.get("email") or f"{username}@{base_dn.replace('dc=', '').replace(',', '.')}"],
            "ou": [ou],
            "memberof": member_of
        }

        if user.get("telephone"):
            entry["telephonenumber"] = [user.get("telephone")]
        if user.get("department"):
            entry["departmentnumber"] = [user.get("department")]

        return entry

    def _build_group_ldap_entry(self, group, base_dn):
        """Convert MySQL group row to standard LDAP group attribute dictionary."""
        gname = group.get("name", "")
        dn = f"cn={gname},ou=groups,{base_dn}"

        member_uids = group.get("member_uids") or ""
        members = []
        uids = []
        if member_uids:
            for u in member_uids.split(","):
                u = u.strip()
                if u:
                    uids.append(u)
                    members.append(f"uid={u},ou=users,{base_dn}")

        entry = {
            "dn": dn,
            "objectclass": ["top", "groupOfNames", "posixGroup"],
            "cn": [gname],
            "description": [group.get("description") or ""],
            "member": members if members else [f"cn=admin,{base_dn}"],
            "memberuid": uids
        }
        if group.get("gid_number"):
            entry["gidnumber"] = [str(group.get("gid_number"))]

        return entry

    def _create_search_entry_response(self, msg_id, entry_dict, requested_attrs):
        """Build SearchResultEntry LDAPMessage."""
        resp_msg = rfc2251.LDAPMessage()
        resp_msg.setComponentByName("messageID", msg_id)

        entry = rfc2251.SearchResultEntry()
        entry.setComponentByName("objectName", entry_dict["dn"])

        attrs = rfc2251.SearchResultEntry()["attributes"]
        attr_idx = 0

        # Return all attributes if none requested, or only requested ones
        want_all = not requested_attrs or "*" in requested_attrs or "1.1" in requested_attrs

        for attr_key, attr_vals in entry_dict.items():
            if attr_key == "dn":
                continue

            if not want_all and attr_key.lower() not in requested_attrs:
                continue

            attr = rfc2251.Attribute()
            attr.setComponentByName("type", attr_key)
            vals = attr["vals"]
            for v_idx, val in enumerate(attr_vals):
                val_obj = rfc2251.AttributeValue(str(val))
                vals.setComponentByPosition(v_idx, val_obj)
            attr.setComponentByName("vals", vals)

            attrs.setComponentByPosition(attr_idx, attr)
            attr_idx += 1

        entry.setComponentByName("attributes", attrs)

        op = rfc2251.LDAPMessage()["protocolOp"]
        op.setComponentByName("searchResEntry", entry)
        resp_msg.setComponentByName("protocolOp", op)

        return encoder.encode(resp_msg)

    def _create_search_done_response(self, msg_id, result_code):
        """Build SearchResultDone LDAPMessage."""
        resp_msg = rfc2251.LDAPMessage()
        resp_msg.setComponentByName("messageID", msg_id)

        done = rfc2251.SearchResultDone()
        done.setComponentByName("resultCode", result_code)
        done.setComponentByName("matchedDN", "")
        done.setComponentByName("errorMessage", "")

        op = rfc2251.LDAPMessage()["protocolOp"]
        op.setComponentByName("searchResDone", done)
        resp_msg.setComponentByName("protocolOp", op)

        return encoder.encode(resp_msg)

    def _matches_filter(self, filter_obj, entry_dict):
        """
        Evaluate if an LDAP entry matches the Filter structure recursively.
        """
        if filter_obj is None:
            return True

        filter_type = filter_obj.getName()

        if filter_type == "equalityMatch":
            ava = filter_obj.getComponent()
            attr = str(ava.getComponentByName("attributeDesc")).lower().strip()
            val = str(ava.getComponentByName("assertionValue")).lower().strip()

            if attr == "dn":
                return entry_dict.get("dn", "").lower() == val

            entry_vals = [str(x).lower() for x in entry_dict.get(attr, [])]
            return val in entry_vals

        elif filter_type == "present":
            attr = str(filter_obj.getComponent()).lower().strip()
            return attr in entry_dict and len(entry_dict[attr]) > 0

        elif filter_type == "substrings":
            sub_filter = filter_obj.getComponent()
            attr = str(sub_filter.getComponentByName("type")).lower().strip()
            entry_vals = [str(x).lower() for x in entry_dict.get(attr, [])]
            if not entry_vals:
                return False

            sub_seq = sub_filter.getComponentByName("substrings")
            for i in range(len(sub_seq)):
                sub_comp = sub_seq.getComponentByPosition(i)
                sub_type = sub_comp.getName()
                sub_val = str(sub_comp.getComponent()).lower()

                matched_any = False
                for ev in entry_vals:
                    if sub_type == "initial" and ev.startswith(sub_val):
                        matched_any = True
                    elif sub_type == "any" and sub_val in ev:
                        matched_any = True
                    elif sub_type == "final" and ev.endswith(sub_val):
                        matched_any = True
                if not matched_any:
                    return False
            return True

        elif filter_type == "and":
            filter_set = filter_obj.getComponent()
            for i in range(len(filter_set)):
                sub_f = filter_set.getComponentByPosition(i)
                if not self._matches_filter(sub_f, entry_dict):
                    return False
            return True

        elif filter_type == "or":
            filter_set = filter_obj.getComponent()
            if len(filter_set) == 0:
                return True
            for i in range(len(filter_set)):
                sub_f = filter_set.getComponentByPosition(i)
                if self._matches_filter(sub_f, entry_dict):
                    return True
            return False

        elif filter_type == "not":
            sub_f = filter_obj.getComponent()
            return not self._matches_filter(sub_f, entry_dict)

        # Default fallback for unhandled complex filter types
        return True

    def _filter_to_string(self, filter_obj):
        """Convert pyasn1 filter object to human-readable LDAP filter string."""
        if filter_obj is None:
            return "(objectClass=*)"

        try:
            filter_type = filter_obj.getName()
            if filter_type == "equalityMatch":
                ava = filter_obj.getComponent()
                return f"({ava.getComponentByName('attributeDesc')}={ava.getComponentByName('assertionValue')})"
            elif filter_type == "present":
                return f"({filter_obj.getComponent()}=*)"
            elif filter_type == "and":
                parts = [self._filter_to_string(filter_obj.getComponent().getComponentByPosition(i)) for i in range(len(filter_obj.getComponent()))]
                return "(&" + "".join(parts) + ")"
            elif filter_type == "or":
                parts = [self._filter_to_string(filter_obj.getComponent().getComponentByPosition(i)) for i in range(len(filter_obj.getComponent()))]
                return "(|" + "".join(parts) + ")"
            elif filter_type == "not":
                return "(!" + self._filter_to_string(filter_obj.getComponent()) + ")"
            elif filter_type == "substrings":
                sub = filter_obj.getComponent()
                return f"({sub.getComponentByName('type')}=*)"
        except Exception:
            pass

        return "(filter)"
