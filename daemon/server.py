"""
Custom LDAP Server Daemon Entry Point.
Runs a multi-threaded TCP socket server on port 389 (or configured port),
accepts connections from LDAP clients (pGina, apps, CLI), and handles
requests using LdapHandler and DatabaseManager.
"""

import sys
import os
import json
import socket
import threading
import logging
import time

from db import DatabaseManager
from ldap_handler import LdapHandler

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] [%(name)s] %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S"
)
logger = logging.getLogger("LDAP_SERVER")

STATUS_FILE = os.path.join(os.path.dirname(__file__), "server_status.json")


class ClientHandler(threading.Thread):
    def __init__(self, client_sock, client_addr, ldap_handler):
        super().__init__(daemon=True)
        self.sock = client_sock
        self.addr = client_addr
        self.handler = ldap_handler
        self.running = True

    def run(self):
        client_ip = self.addr[0]
        logger.info(f"New LDAP connection from {client_ip}:{self.addr[1]}")
        self.sock.settimeout(120)  # 2 minute idle timeout

        buffer = bytearray()
        try:
            while self.running:
                chunk = self.sock.recv(4096)
                if not chunk:
                    break

                buffer.extend(chunk)

                # Process LDAP packets
                responses = self.handler.process_data(bytes(buffer), client_ip=client_ip)
                buffer.clear()

                for resp_chunk in responses:
                    self.sock.sendall(resp_chunk)

        except socket.timeout:
            logger.debug(f"Connection timed out for {client_ip}")
        except ConnectionResetError:
            logger.debug(f"Connection reset by client {client_ip}")
        except Exception as e:
            logger.error(f"Error handling client {client_ip}: {e}")
        finally:
            try:
                self.sock.close()
            except Exception:
                pass
            logger.info(f"Connection closed for {client_ip}:{self.addr[1]}")


class LdapServer:
    def __init__(self):
        self.db = DatabaseManager()
        self.handler = LdapHandler(self.db)
        self.host = self.db.config.get("ldap_host", "0.0.0.0")
        self.port = int(self.db.get_setting("ldap_port", self.db.config.get("ldap_port", 389)))
        self.server_sock = None
        self.running = False
        self.start_time = None

    def update_status_file(self, is_running):
        status = {
            "running": is_running,
            "host": self.host,
            "port": self.port,
            "base_dn": self.db.get_base_dn(),
            "pid": os.getpid() if is_running else None,
            "started_at": time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(self.start_time)) if self.start_time else None,
            "last_heartbeat": time.strftime("%Y-%m-%d %H:%M:%S")
        }
        try:
            with open(STATUS_FILE, "w", encoding="utf-8") as f:
                json.dump(status, f, indent=2)
        except Exception as e:
            logger.error(f"Failed to update status file: {e}")

    def start(self):
        self.running = True
        self.start_time = time.time()

        try:
            self.server_sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            self.server_sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
            self.server_sock.bind((self.host, self.port))
            self.server_sock.listen(128)
            logger.info("=" * 60)
            logger.info(f"🚀 Custom LDAP Server is RUNNING on {self.host}:{self.port}")
            logger.info(f"📁 Base DN: {self.db.get_base_dn()}")
            logger.info(f"🔑 Admin Bind DN: {self.db.get_admin_bind_dn()}")
            logger.info(f"🌐 Anonymous Bind: {'ENABLED' if self.db.is_anonymous_allowed() else 'DISABLED'}")
            logger.info("=" * 60)

            self.update_status_file(True)

            # Background heartbeat thread
            def heartbeat():
                while self.running:
                    self.update_status_file(True)
                    time.sleep(5)

            hb_thread = threading.Thread(target=heartbeat, daemon=True)
            hb_thread.start()

            while self.running:
                try:
                    client_sock, client_addr = self.server_sock.accept()
                    client_handler = ClientHandler(client_sock, client_addr, self.handler)
                    client_handler.start()
                except socket.error as e:
                    if self.running:
                        logger.error(f"Accept socket error: {e}")
                        time.sleep(0.1)

        except PermissionError:
            logger.critical(f"❌ Permission denied binding to port {self.port}. Run as Administrator or change port to 1389.")
            self.update_status_file(False)
        except OSError as e:
            logger.critical(f"❌ OS Error binding to port {self.port}: {e}. (Port might already be in use).")
            self.update_status_file(False)
        except Exception as e:
            logger.critical(f"❌ Unexpected server error: {e}")
            self.update_status_file(False)
        finally:
            self.stop()

    def stop(self):
        if not self.running:
            return
        self.running = False
        logger.info("Stopping LDAP server...")
        if self.server_sock:
            try:
                self.server_sock.close()
            except Exception:
                pass
        self.update_status_file(False)
        logger.info("LDAP server stopped.")


if __name__ == "__main__":
    server = LdapServer()
    try:
        server.start()
    except KeyboardInterrupt:
        logger.info("Server interrupted by user.")
        server.stop()
        sys.exit(0)
