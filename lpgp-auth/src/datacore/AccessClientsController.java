package datacore;

import config.SocketConfig;

import java.lang.Exception;
import java.sql.*;
import java.lang.ClassNotFoundException;
import java.util.ArrayList;

public class AccessClientsController extends Connector{

    private ClientsController verifier = null;

    public static class ClientReferenceError extends Exception{

        public ClientReferenceError(String clientNm){ super("Invalid client named: " + clientNm); }

        public ClientReferenceError(int clientPk){ super("Invalid client PK: " + clientPk); }
    }

    public AccessClientsController(ClientsController verifier, String configFile, String host, String db) throws
            AlreadyConnectedError, ExternalDriverError, SocketConfig.fetchingError,
            SocketConfig.ConfigurationsAlreadyLoaded, ClassNotFoundException{
        super(configFile, host, db);
        this.verifier = verifier;
    }

    public void addAccessRecord(int clientPk, boolean success) throws NotConnectedError, ClientReferenceError, SQLException{
        if(!this.gotConnection) throw new NotConnectedError();
        if(!this.verifier.checkClientExists(clientPk)) throw new ClientReferenceError(clientPk);
        int sc = success ? 1 : 0;
        PreparedStatement psmt = this.connectionMain.prepareStatement("INSERT INTO tb_access (id_client, vl_success) VALUES (?, ?);");
        psmt.setInt(0, clientPk);
        psmt.setInt(1, sc);
        int ner = psmt.executeUpdate();
    }

    public void addAccessRecord(String clientName, boolean success) throws NotConnectedError, ClientReferenceError, SQLException{
        if(!this.gotConnection) throw new NotConnectedError();
        int cd;
        int sc = success ? 1 : 0;
        try{ cd = this.verifier.getPkByName(clientName);}
        catch(ClientsController.ClientNotFoundError cl){ throw new ClientReferenceError(clientName);}
        PreparedStatement psmt = this.connectionMain.prepareStatement("INSERT INTO tb_access (id_client, vl_success) VALUES (?, ?);");
        psmt.setInt(0, cd);
        psmt.setInt(1, sc);
        int ner = psmt.executeUpdate();
    }

    // debug
    public ArrayList<String[]> listAccesses() throws NotConnectedError, SQLException{
        if(!this.gotConnection) throw new NotConnectedError();
        ArrayList<String[]> accesses = new ArrayList<String[]>();
        Statement stmt = this.connectionMain.createStatement();
        ResultSet results = stmt.executeQuery("SELECT * FROM tb_access;");
        while(results.next()){
            String[] record = new String[4];
            record[0] = "" + results.getInt(0); // cd_reg
            record[1] = "" + results.getInt(1); // id_client
            record[2] = results.getTimestamp(2).toString();  // dt_reg
            record[3] = results.getInt(3) == 1 ? "VALID" : "INVALID"; // vl_success
            accesses.add(record);
        }
        return accesses;
    }
}
