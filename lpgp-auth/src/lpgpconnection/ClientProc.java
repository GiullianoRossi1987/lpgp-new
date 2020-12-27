package lpgpconnection;

import java.net.Socket;
import java.lang.Thread;
import java.io.*;
import java.lang.Exception;


public class ClientProc extends Thread{
	protected Socket client;
	protected boolean gotClient = false;
	
	public static class InternalConnectionError extends Exception{
		
		public InternalConnectionError(String msg){ super("Error in the internal connection: " + msg); }
	}
	
	public static class ClientAlreadyReceived extends Exception{
		
		public ClientAlreadyReceived(){ super("Can't receive other client!"); }
	}
	
	public static class NoSuchClient extends Exception{
		
		public NoSuchClient(){ super("There's no client loaded!"); }
	}
	
	public ClientProc(Socket client) throws ClientAlreadyReceived{
		if(this.gotClient) throw new ClientAlreadyReceived();
		this.client = client;
		this.gotClient = true;
	}
	
	public ClientProc(){
	
	}
	
	public void debugRun() throws NoSuchClient, InternalConnectionError{
		if(!this.gotClient) throw new NoSuchClient();
		try{
			PrintWriter sender = new PrintWriter(this.client.getOutputStream(), true);
			DataInputStream receiver = new DataInputStream(this.client.getInputStream());
			sender.println("Welcome");
			byte[] msg = new byte[1024];
			int i = receiver.read(msg);
			for(byte chr : msg){
				System.out.print((char)chr);
			}
			this.client.close();
		}
		catch(Exception e){ throw new InternalConnectionError(e.getMessage());}
	}
	
	public void run(){
		try{ this.debugRun();}
		catch(Exception e){ e.printStackTrace();}
	}
	
}