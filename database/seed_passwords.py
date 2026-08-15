import bcrypt
import pymysql

def seed():
    conn = pymysql.connect(
        host="127.0.0.1",
        port=3306,
        user="root",
        password="",
        database="ldap_server_db",
        charset="utf8mb4",
        autocommit=True
    )
    cursor = conn.cursor()

    # Passwords:
    # admin (bind account): admin123
    # budi & siti: password123
    # admin (web user): adminpassword
    h_admin = bcrypt.hashpw(b"admin123", bcrypt.gensalt()).decode("utf-8")
    h_user = bcrypt.hashpw(b"password123", bcrypt.gensalt()).decode("utf-8")
    h_webadmin = bcrypt.hashpw(b"adminpassword", bcrypt.gensalt()).decode("utf-8")

    cursor.execute("UPDATE ldap_settings SET setting_value = %s WHERE setting_key = 'admin_password_hash'", (h_admin,))
    cursor.execute("UPDATE ldap_users SET password_hash = %s WHERE username IN ('budi', 'siti')", (h_user,))
    cursor.execute("UPDATE ldap_users SET password_hash = %s WHERE username = 'admin'", (h_webadmin,))

    print("[+] Successfully updated default password hashes!")
    cursor.close()
    conn.close()

if __name__ == "__main__":
    seed()
