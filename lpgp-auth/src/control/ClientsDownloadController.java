package control;

import org.json.JSONObject;
import org.json.JSONArray;
import java.io.IOException;
import java.lang.Exception;
import datacore.ClientsController;
import datacore.Connector.NotConnectedError;
import java.sql.SQLException;
import datacore.MaskedData;

public class ClientsDownloadController extends BaseController{

    // recorder references
    public static final String CLIENTS_DOWNLOAD_ = "cdownloads";
    public static final String CLIENT_PK_REF_ = "client";
    public static final String CLIENT_TK_REF_ = "token";
    public static final String CLIENT_DT_REF_ = "timestamp";


    // external content references
    public static final String EXT_CPK = "Client";
    public static final String EXT_CDT = "Dt";
    public static final String EXT_CTK = "cdtk";

    private ClientsController verifier = null;


    public static class DownloadTokenNotFound extends Exception{

        public DownloadTokenNotFound(String token){ super("No such token: " + token);}
    }

    public static class ClientReferenceError extends Exception{

        public ClientReferenceError(String ref){ super("No such client: " + ref);}

        public ClientReferenceError(int ref){ super("No such client: " + ref); }
    }

    public static class TimestampRefError extends Exception{

        public TimestampRefError(String timestamp){ super("No such download at data: " + timestamp);}
    }

    private boolean checkDownloadToken(String token) throws ControlRecorderNotLoaded{
        if(!this.gotControl) throw new ControlRecorderNotLoaded();
        JSONArray mainContent = this.controlMain.getJSONArray(CLIENTS_DOWNLOAD_);
        for(int i = 0; i < mainContent.length(); i++){
            JSONObject record = mainContent.getJSONObject(i);
            String tk = record.getString(CLIENT_TK_REF_);
            if(token.equals(tk)) return true;
        }
        return false;
    }

    private boolean checkClientReferenceExists(int client_ref) throws ControlRecorderNotLoaded, NotConnectedError,
            SQLException{
        if(!this.gotControl) throw new ControlRecorderNotLoaded();
        if(!this.verifier.checkClientExists(client_ref)) return false;
        JSONArray records = this.controlMain.getJSONArray(CLIENTS_DOWNLOAD_);
        for(int i = 0; i < records.length(); i++){
            JSONObject record = records.getJSONObject(i);
            if(record.getInt(CLIENT_PK_REF_) == client_ref) return true;
        }
        return false;
    }

    public ClientsController getVerifier(){ return this.gotControl ? this.verifier : null;}

    public void setVerifier(ClientsController verifier){this.verifier = verifier;}

    private boolean checkTimestampExists(String timestamp) throws ControlRecorderNotLoaded{
        if(!this.gotControl) throw new ControlRecorderNotLoaded();
        JSONArray records = this.controlMain.getJSONArray(CLIENTS_DOWNLOAD_);
        for(int i = 0; i < records.length(); i++) {
            JSONObject record = records.getJSONObject(i);
            if (timestamp.equals(record.getString(CLIENT_DT_REF_))) return true;
        }
        return false;
    }

    public boolean authClientsAuthFile(String client_af_path) throws ControlRecorderNotLoaded, DownloadTokenNotFound,
            ClientReferenceError, TimestampRefError, java.sql.SQLException, datacore.ClientsController.NotConnectedError{
        if(!this.gotControl) throw new ControlRecorderNotLoaded();
        MaskedData md = new MaskedData();
        String tm, tk;
        int cl;
        try{
            md.readFile(client_af_path);
            JSONObject content = md.getParsedPure();
            // setup main data
            tm = content.getString(EXT_CDT);
            tk = content.getString(EXT_CTK);
            cl = content.getInt(EXT_CPK);
        }
        catch(Exception e) {return false;}
        if(!this.checkDownloadToken(tk)) throw new DownloadTokenNotFound(tk);
        if(!this.checkClientReferenceExists(cl)) throw new ClientReferenceError(cl);
        if(!this.checkTimestampExists(tm)) throw new TimestampRefError(tm);
        return true;
    }

    public boolean authClientsData(String tm, String tk, int cl) throws ControlRecorderNotLoaded, DownloadTokenNotFound,
            ClientReferenceError, TimestampRefError, java.sql.SQLException, datacore.ClientsController.NotConnectedError{
        if(!this.checkDownloadToken(tk)) throw new DownloadTokenNotFound(tk);
        if(!this.checkClientReferenceExists(cl)) throw new ClientReferenceError(cl);
        if(!this.checkTimestampExists(tm)) throw new TimestampRefError(tm);
        return true; // valid enough
    }

    public ClientsDownloadController(ClientsController verifier, String controlFile) throws ControlRecorderAlreadyLoaded,
            IOException{
        super(controlFile);
        this.setVerifier(verifier);
    }
}
