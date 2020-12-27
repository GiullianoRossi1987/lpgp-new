import config.SocketConfig;
import datacore.ClientsController;
import lpgpconnection.*;

public class Main{
	
	public static void main(String[] args) {
		try {
			LpgpServer lpgpServer = new LpgpServer(
					new SocketConfig("src/config/sconfig_schema.json"),
					new ClientsController("src/config/sconfig_schema.json", "127.0.0.1", "LPGP_WEB")
			);
			lpgpServer.start();
		}
		catch (Exception e) {
			e.printStackTrace();
		}
	}
}