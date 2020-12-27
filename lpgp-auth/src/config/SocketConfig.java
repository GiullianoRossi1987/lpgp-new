package config;

import org.json.*;
import java.lang.Exception;
import java.io.*;
import java.util.Scanner;
import org.jetbrains.annotations.*;

/**
 * Works reading and converting the configurations file of the official server
 * That configurations file must have a specific structure
 * */
public class SocketConfig {

    private String configLoaded = null;
    private boolean gotConfig = false;

    private String serverName = null;
    private int serverPort = 0;
    private String Talkback = null;
    private String[] internalAccount = null;
    private String[] normalClient = null;
    private String[] rootClient = null;
    private String recorder = null;

    public static class ConfigurationsAlreadyLoaded extends Exception{

        public ConfigurationsAlreadyLoaded(){ super("There's a configurations file already loaded");}
    }

    public static class ConfigurationsNotLoaded extends Exception{

        public ConfigurationsNotLoaded(){ super("Can't access a configurations file, it isn't loaded");}
    }

    public static class fetchingError extends Exception{

        public fetchingError(String error){ super("Couldn't fetch configurations file, ERROR: " + error);}
    }

    private void fetchContent(String content) throws fetchingError{
        try {
            JSONObject jsonContent = new JSONObject(content);
            JSONObject databaseData = (JSONObject) jsonContent.get("Database");
            JSONArray internalUser = databaseData.getJSONArray("Internal");
            JSONArray normalClient = databaseData.getJSONArray("Normal");
            JSONArray rootClient = databaseData.getJSONArray("Root");
            // setup
            this.serverPort = jsonContent.getInt("ServerPort");
            this.serverName = jsonContent.getString("ServerName");
            this.Talkback = jsonContent.getString("TalkbackMsg");
            this.recorder = jsonContent.getString("Recorder");
            this.internalAccount = new String[2];
            this.normalClient = new String[2];
            this.rootClient = new String[2];

            this.internalAccount[0] = internalUser.getString(0);
            this.internalAccount[1] = internalUser.getString(1);

            this.normalClient[0] = normalClient.getString(0);
            this.normalClient[1] = normalClient.getString(1);

            this.rootClient[0] = rootClient.getString(0);
            this.rootClient[1] = rootClient.getString(1);

            this.gotConfig = true;
        }
        catch(Exception error){
            throw new fetchingError(error.getMessage());
        }
    }

    public void loadConfig(String configFile) throws ConfigurationsAlreadyLoaded, IOException, fetchingError{
        if(this.gotConfig) throw new ConfigurationsAlreadyLoaded();
        File configLoaded = new File(configFile);
        Scanner configReader = new Scanner(configLoaded);
        StringBuilder configRawBd = new StringBuilder();
        while(configReader.hasNext())
            configRawBd.append(configReader.nextLine());
        String configRaw = configRawBd.toString();
        this.fetchContent(configRaw);
        this.configLoaded = configFile;
    }

    public void unloadConfig() throws ConfigurationsNotLoaded{
        if(!this.gotConfig) throw new ConfigurationsNotLoaded();
        this.configLoaded = null;
        this.serverName = null;
        this.serverPort = 0;
        this.Talkback = null;
        this.internalAccount = null;
        this.rootClient = null;
        this.normalClient = null;
        this.gotConfig = false;
    }

    public SocketConfig(String configurations) throws ConfigurationsAlreadyLoaded, IOException, fetchingError{
        this.loadConfig(configurations);
    }

    public String getServerName() throws ConfigurationsNotLoaded{
        if(!this.gotConfig) throw new ConfigurationsNotLoaded();
        return this.serverName;
    }

    public int getServerPort() throws ConfigurationsNotLoaded{
        if(!this.gotConfig || this.serverPort == 0) throw new ConfigurationsNotLoaded();
        return this.serverPort;
    }

    public String[] getInternalAccount() throws ConfigurationsNotLoaded{
        if(!this.gotConfig) throw new ConfigurationsNotLoaded();
        return this.internalAccount;
    }

    public String[] getRootClient() throws ConfigurationsNotLoaded{
        if(!this.gotConfig) throw new ConfigurationsNotLoaded();
        return this.rootClient;
    }

    public String[] getNormalClient() throws ConfigurationsNotLoaded{
        if(!this.gotConfig) throw new ConfigurationsNotLoaded();
        return this.normalClient;
    }

    public String getRecorder() throws ConfigurationsNotLoaded{
        if(!this.gotConfig) throw new ConfigurationsNotLoaded();
        else return this.recorder;
    }
}
