package datacore;

import java.lang.Exception;
import java.sql.*;
import java.lang.ClassNotFoundException;
import config.SocketConfig;
import control.ClientsDownloadController;
import org.json.*;

public class ClientsController extends Connector{
	
	public static class ClientTokenAuthenticationError extends Exception{
		
		public ClientTokenAuthenticationError(String authError){
			super("Client token authentication error: " + authError);
		}
	}
	
	public static class AuthenticationError extends Exception{
		
		public AuthenticationError(String msg) { super("Authentication error: " + msg);}
	}
	
	public static class ClientNotFoundError extends Exception{
		
		public ClientNotFoundError(String clientRef){
			super("Couldn't find client, using name reference: " + clientRef);
		}
	}
	
	public static class ProprietaryReferenceError extends Exception{
		
		public ProprietaryReferenceError(int propRef){ super("Invalid proprietary " + propRef);}
	}
	
	public ClientsController(String configurationsFile, String host, String db) throws AlreadyConnectedError,
			ExternalDriverError, ClassNotFoundException, SocketConfig.fetchingError, SocketConfig.ConfigurationsAlreadyLoaded{
		super(configurationsFile, host, db);
	}
	
	public boolean checkClientExists(String nameReference) throws NotConnectedError, SQLException{
		if(!this.gotConnection) throw new NotConnectedError();
		Statement cursor = this.connectionMain.createStatement();
		ResultSet totalClients = cursor.executeQuery("SELECT COUNT(cd_client) AS \"countage\" FROM tb_clients WHERE nm_client = \"" + nameReference + "\" LIMIT 1;");
		return totalClients.next() && totalClients.getInt("countage") > 0;
	}
	
	public boolean checkClientExists(int ref) throws NotConnectedError, SQLException{
		if(!this.gotConnection) throw new NotConnectedError();
		Statement cursor = this.connectionMain.createStatement();
		ResultSet totalClients = cursor.executeQuery("SELECT COUNT(cd_client) AS \"countage\" FROM tb_clients WHERE cd_client =" + ref + ";");
		return totalClients.next() && totalClients.getInt("countage") > 0;
	}
	
	private boolean checkProprietaryReference(int prop) throws NotConnectedError, SQLException{
		if(!this.gotConnection) throw new NotConnectedError();
		Statement cursor = this.connectionMain.createStatement();
		ResultSet countage = cursor.executeQuery("SELECT COUNT(cd_proprietary) AS 'countage' FROM tb_proprietaries WHERE cd_proprietary = " + prop + ";");
		return countage.next() && countage.getInt("countage") > 0;
	}
	
	private boolean authTokenClient(String tk, int clientId) throws NotConnectedError, SQLException, ClientNotFoundError{
		if(!this.gotConnection) throw new NotConnectedError();
		if(!this.checkClientExists(clientId)) throw new ClientNotFoundError(clientId + "");
		Statement stmt = this.connectionMain.createStatement();
		ResultSet rs = stmt.executeQuery("SELECT tk_client FROM tb_clients WHERE cd_client = " + clientId + ";");
		// only one row
		String originalTk = "";
		if(rs.next()) originalTk = rs.getString("tk_client");
		else return false;
		return originalTk.equals(tk);
	}
	
	public boolean authClientData(int clientId, String date, String cdtk, String tk, int prop) throws NotConnectedError,
			SQLException, ClientNotFoundError, ProprietaryReferenceError,  AuthenticationError, ClientTokenAuthenticationError{
		if(!this.gotConnection) throw new NotConnectedError();
		if(!this.checkClientExists(clientId)) throw new ClientNotFoundError("" + clientId);
		if(!this.authTokenClient(tk, clientId)) throw new ClientTokenAuthenticationError("INVALID CLIENT TOKEN");
		if(!this.checkProprietaryReference(prop)) throw new ProprietaryReferenceError(prop);
		try{
			ClientsDownloadController cdc = new ClientsDownloadController(this, this.configurationsLocal.getRecorder());
			return cdc.authClientsData(date, cdtk, clientId);
		}
		catch (Exception e) {throw new AuthenticationError(e.getMessage()); }
	}
	
	public int getPkByName(String name) throws NotConnectedError, SQLException, ClientNotFoundError{
		if(!this.gotConnection) throw new NotConnectedError();
		if(!this.checkClientExists(name)) throw new ClientNotFoundError(name);
		Statement stmt = this.connectionMain.createStatement();
		ResultSet rt = stmt.executeQuery("SELECT cd_client FROM tb_clients WHERE nm_client = \"" + name +"\";");
		if(rt.next()) return rt.getInt("cd_client");
		else return 0;
	}
	
	public boolean authClientFile(String clientPath) throws NotConnectedError,
			SQLException, ClientNotFoundError, ProprietaryReferenceError,  AuthenticationError, ClientTokenAuthenticationError,
			MaskedData.DataNotLoaded {
		MaskedData md;
		try{
			md = new MaskedData();
			md.readFile(clientPath);
		}
		catch(Exception e){ throw new AuthenticationError(e.getMessage());}
		JSONObject content = md.getParsedPure();
		String tk = content.getString("Token");
		String dt = content.getString("Dt");
		String dk = content.getString("cdtk");
		int client = content.getInt("Client");
		int prop = content.getInt("Proprietary");
		return this.authClientData(client, dt, dk, tk, prop);
	}
	
	public boolean isClientRoot(String maskedClient) throws NotConnectedError, MaskedData.DataDecodingError, ClientNotFoundError{
		if(!this.gotConnection) throw new NotConnectedError();
		try {
			MaskedData unmasker = new MaskedData(maskedClient);
			JSONObject data = unmasker.getParsedPure();
			int client = data.getInt("Client");
			// gets to the database
			if(!this.checkClientExists(client)) throw new ClientNotFoundError(client + "");
			Statement stmt = this.connectionMain.createStatement();
			ResultSet rs = stmt.executeQuery("SELECT vl_root FROM tb_clients WHERE cd_client = " + client + ";");
			return rs.next() && rs.getInt("vl_root") == 1;
		}
		catch(ClientNotFoundError cnfe){ throw new ClientNotFoundError(cnfe.getMessage()); }
		catch(Exception e){ throw new MaskedData.DataDecodingError(e.getMessage()); }
	}
	
	public boolean isClientRoot(int client) throws NotConnectedError, ClientNotFoundError, SQLException{
		if(!this.gotConnection) throw new NotConnectedError();
		if(!this.checkClientExists(client)) throw new ClientNotFoundError(client + "");
		Statement stmt = this.connectionMain.createStatement();
		ResultSet rs = stmt.executeQuery("SELECT vl_root FROM tb_clients WHERE cd_client = " + client + ";");
		return rs.next() && rs.getInt("vl_root") == 1;
	}
	
	public boolean authClientMask(String mask) throws NotConnectedError,
		SQLException, ClientNotFoundError, ProprietaryReferenceError,  AuthenticationError, ClientTokenAuthenticationError, MaskedData.DataNotLoaded{
		MaskedData md;
		try{ md = new MaskedData(mask);}
		catch(Exception e){ throw new AuthenticationError(e.getMessage());}
		JSONObject content = md.getParsedPure();
		String tk = content.getString("Token");
		String dt = content.getString("Dt");
		String dk = content.getString("cdtk");
		int client = content.getInt("Client");
		int prop = content.getInt("Proprietary");
		return this.authClientData(client, dt, dk, tk, prop);
	}
}
