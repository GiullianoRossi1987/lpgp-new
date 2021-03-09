package datacore;

import java.lang.Exception;
import java.lang.ClassNotFoundException;
import config.SocketConfig;
import config.SocketConfig.fetchingError;
import config.SocketConfig.ConfigurationsAlreadyLoaded;
import java.sql.*;

public class Connector{

    protected Connection connectionMain = null;
    protected boolean gotConnection = false;
    protected String JdbcLink = null;
    protected SocketConfig configurationsLocal = null;

    protected String userUsing = null;
    protected String passwdUsing = null;

    public static class AlreadyConnectedError extends Exception{

        public AlreadyConnectedError(){ super("The database connector is already connected!");}
    }

    public static class NotConnectedError extends Exception{

        public NotConnectedError(){ super("The database connector isn't connected yet!");}
    }

    public static class ExternalDriverError extends Exception{

        public ExternalDriverError(String driverMessage){
            super("The MySQL database driver thrown that error: " + driverMessage + "");
        }
    }

    public void loadConfig(String configurationsFile) throws fetchingError, ConfigurationsAlreadyLoaded{
        try{
            this.configurationsLocal = new SocketConfig(configurationsFile);

        }
        catch(java.io.IOException e){
            throw new fetchingError(e.getMessage());
        }
    }

    public void connectTo(String host, String username, String passwd, String db) throws AlreadyConnectedError,
            ExternalDriverError, ClassNotFoundException{
        if(this.gotConnection) throw new AlreadyConnectedError();
        try{
            Class.forName("com.mysql.jdbc.Driver");
            String generatedLink = "jdbc:mysql://" + host + ":3306/" + db + "?characterEncoding=UTF-8";
            this.connectionMain = DriverManager.getConnection(generatedLink, username, passwd);
            this.gotConnection = true;
            this.JdbcLink = generatedLink;
            this.userUsing = username;
            this.passwdUsing = passwd;
        }
        catch(SQLException sqle) {
            throw new ExternalDriverError(sqle.getMessage());
        }
    }

    public void handledConnection(String configurationsFile, String host, String db) throws AlreadyConnectedError,
            ExternalDriverError, fetchingError, ConfigurationsAlreadyLoaded, ClassNotFoundException{
        if(this.gotConnection) throw new AlreadyConnectedError();
        try{
            Class.forName("com.mysql.jdbc.Driver");
            this.loadConfig(configurationsFile);
            String username = this.configurationsLocal.getInternalAccount()[0];
            String passwd = this.configurationsLocal.getInternalAccount()[1];
            this.connectTo(host, username, passwd, db);
        }
        catch(config.SocketConfig.ConfigurationsNotLoaded cnle){
            throw new fetchingError(cnle.getMessage());
        }
    }

    public Connector(String configurationsFile, String host, String db) throws AlreadyConnectedError,
            ExternalDriverError, fetchingError, ConfigurationsAlreadyLoaded, ClassNotFoundException{
        // start constructor
        this.handledConnection(configurationsFile, host, db);
    }

    public Connector(String host, String db, String username, String passwd) throws AlreadyConnectedError,
            ExternalDriverError, ClassNotFoundException{
        this.connectTo(host, username, passwd, db);
    }

    public Connection getConnectionMain() throws NotConnectedError{
        if(!this.gotConnection) throw new NotConnectedError();
        else return this.connectionMain;
    }

    public SocketConfig getActualConfig() throws NotConnectedError{
        if(!this.gotConnection) throw new NotConnectedError();
        else return this.configurationsLocal;
    }
}
