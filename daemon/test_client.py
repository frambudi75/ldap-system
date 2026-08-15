"""
LDAP Test Client Utility.
Performs test Bind and Search operations against the running LDAP server.
"""

import sys
import socket
from pyasn1.codec.ber import encoder, decoder
from pyasn1_modules import rfc2251


def run_ldap_test(host="127.0.0.1", port=389):
    print(f"[*] Testing connection to LDAP Server at {host}:{port}...")

    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(5)
        sock.connect((host, port))
        print("[+] TCP Connection successful!")
    except Exception as e:
        print(f"[-] Failed to connect to {host}:{port}: {e}")
        return False

    msg_id = 1

    # 1. Test Admin Bind
    print("\n--- Test 1: Admin Simple Bind ---")
    bind_msg = rfc2251.LDAPMessage()
    bind_msg.setComponentByName("messageID", msg_id)

    req = rfc2251.BindRequest()
    req.setComponentByName("version", 3)
    req.setComponentByName("name", "cn=admin,dc=internal,dc=net")
    auth = req["authentication"]
    auth.setComponentByName("simple", "admin123")
    req.setComponentByName("authentication", auth)

    op = rfc2251.LDAPMessage()["protocolOp"]
    op.setComponentByName("bindRequest", req)
    bind_msg.setComponentByName("protocolOp", op)

    sock.sendall(encoder.encode(bind_msg))
    data = sock.recv(4096)
    resp, _ = decoder.decode(data, asn1Spec=rfc2251.LDAPMessage())
    bind_res = resp.getComponentByName("protocolOp").getComponent()
    res_code = int(bind_res.getComponentByName("resultCode"))
    print(f"Admin Bind Result: {'SUCCESS (0)' if res_code == 0 else f'FAILED ({res_code})'}")

    # 2. Test User Bind (budi / password123)
    msg_id += 1
    print("\n--- Test 2: User Bind (uid=budi,ou=users,dc=internal,dc=net) ---")
    bind_msg2 = rfc2251.LDAPMessage()
    bind_msg2.setComponentByName("messageID", msg_id)

    req2 = rfc2251.BindRequest()
    req2.setComponentByName("version", 3)
    req2.setComponentByName("name", "uid=budi,ou=users,dc=internal,dc=net")
    auth2 = req2["authentication"]
    auth2.setComponentByName("simple", "password123")
    req2.setComponentByName("authentication", auth2)

    op2 = rfc2251.LDAPMessage()["protocolOp"]
    op2.setComponentByName("bindRequest", req2)
    bind_msg2.setComponentByName("protocolOp", op2)

    sock.sendall(encoder.encode(bind_msg2))
    data2 = sock.recv(4096)
    resp2, _ = decoder.decode(data2, asn1Spec=rfc2251.LDAPMessage())
    bind_res2 = resp2.getComponentByName("protocolOp").getComponent()
    res_code2 = int(bind_res2.getComponentByName("resultCode"))
    print(f"User Bind Result: {'SUCCESS (0)' if res_code2 == 0 else f'FAILED ({res_code2})'}")

    # 3. Test Search Request
    msg_id += 1
    print("\n--- Test 3: Search Request (base=dc=internal,dc=net, filter=uid=budi) ---")
    search_msg = rfc2251.LDAPMessage()
    search_msg.setComponentByName("messageID", msg_id)

    s_req = rfc2251.SearchRequest()
    s_req.setComponentByName("baseObject", "dc=internal,dc=net")
    s_req.setComponentByName("scope", 2)  # wholeSubtree
    s_req.setComponentByName("derefAliases", 0)
    s_req.setComponentByName("sizeLimit", 100)
    s_req.setComponentByName("timeLimit", 10)
    s_req.setComponentByName("typesOnly", False)

    # Filter: (uid=budi)
    ava = rfc2251.Filter().getComponentType()["equalityMatch"].getType().clone()
    ava["attributeDesc"] = "uid"
    ava["assertionValue"] = "budi"
    
    filt = rfc2251.Filter()
    filt["equalityMatch"] = ava
    s_req.setComponentByName("filter", filt)

    s_req.setComponentByName("attributes", rfc2251.SearchRequest()["attributes"])

    s_op = rfc2251.LDAPMessage()["protocolOp"]
    s_op.setComponentByName("searchRequest", s_req)
    search_msg.setComponentByName("protocolOp", s_op)

    sock.sendall(encoder.encode(search_msg))

    # Read responses
    while True:
        data = sock.recv(4096)
        if not data:
            break
        
        offset = 0
        while offset < len(data):
            try:
                resp, rest = decoder.decode(data[offset:], asn1Spec=rfc2251.LDAPMessage())
                offset = len(data) - len(rest)
                op_name = resp.getComponentByName("protocolOp").getName()
                if op_name == "searchResEntry":
                    entry = resp.getComponentByName("protocolOp").getComponent()
                    dn = str(entry.getComponentByName("objectName"))
                    print(f"[Entry Found] DN: {dn}")
                    attrs = entry.getComponentByName("attributes")
                    for i in range(len(attrs)):
                        a = attrs.getComponentByPosition(i)
                        t = str(a.getComponentByName("type"))
                        vals = [str(a.getComponentByName("vals").getComponentByPosition(j)) for j in range(len(a.getComponentByName("vals")))]
                        print(f"   -> {t}: {', '.join(vals)}")
                elif op_name == "searchResDone":
                    print("[Search Done] Search operation completed successfully.")
                    break
            except Exception as e:
                print(f"Decode loop exception: {e}")
                break
        break

    # 4. Unbind
    msg_id += 1
    unbind_msg = rfc2251.LDAPMessage()
    unbind_msg.setComponentByName("messageID", msg_id)
    u_op = rfc2251.LDAPMessage()["protocolOp"]
    u_op.setComponentByName("unbindRequest", rfc2251.UnbindRequest())
    unbind_msg.setComponentByName("protocolOp", u_op)
    sock.sendall(encoder.encode(unbind_msg))
    sock.close()
    print("\n[+] All tests completed successfully!")
    return True


if __name__ == "__main__":
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 389
    run_ldap_test(port=port)
