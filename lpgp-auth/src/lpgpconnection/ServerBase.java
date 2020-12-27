package lpgpconnection;

import java.net.ServerSocket;
import java.net.Socket;
import java.lang.Thread;
import java.lang.Runnable;
import java.lang.Exception;
import org.jetbrains.annotations.*;
import java.nio.Buffer;
import java.util.ArrayList;
import java.io.*;

public class ServerBase implements Runnable{
	
	protected ServerSocket mainServer;
	
	public static class ServerInternalError extends Exception{
		
		public ServerInternalError(String msg){ super(msg); }
	}
	
	public ServerBase(int port) throws ServerInternalError{
		try{
			this.mainServer = new ServerSocket(port);
			new Thread(this).start();
			System.out.println("Ouvindo " + port);
		}
		catch(Exception e){ throw new ServerInternalError(e.getMessage());}
	}
	
	public ServerBase(){
	
	}
	
	public void run(){
		try{
			while(true){
				new ClientProc(this.mainServer.accept()).start();
				System.out.println("Connection done");
			}
		}
		catch(Exception e){ e.printStackTrace();}
	}
	
	public static void main(String[] args){
		try{
			ServerBase sbv = new ServerBase(1987);
			sbv.run();
		}
		catch(Exception e){ e.printStackTrace();}
	}
}