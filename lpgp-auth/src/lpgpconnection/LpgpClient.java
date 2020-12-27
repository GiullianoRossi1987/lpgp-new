package lpgpconnection;

import java.io.*;
import java.net.Socket;
import config.SocketConfig;
import datacore.ClientsController;
import java.lang.Exception;
import org.jetbrains.annotations.*;
import datacore.MaskedData;
import datacore.AccessClientsController;

public class LpgpClient extends ClientProc{
	private ClientsController controller = null;
	private boolean gotController = false;
	public boolean validClient;
	private SocketConfig internalConfig = null;
	private AccessClientsController ac_recorder_ob = null;
	
	public static class ControllerAlreadyLoad extends Exception{
		
		public ControllerAlreadyLoad(){ super("There's a clients controller loaded!"); }
	}
	
	public static class ControllerNotFound extends Exception{
		
		public ControllerNotFound(){ super("There's no client controller loaded");}
	}
	
	public LpgpClient(AccessClientsController ac_rec, SocketConfig conf, ClientsController controller, Socket con) throws ClientProc.ClientAlreadyReceived, ControllerAlreadyLoad{
		super(con);
		System.out.println("System connected to " + con.getInetAddress());
		if(this.gotController) throw new ControllerAlreadyLoad();
		this.controller = controller;
		this.ac_recorder_ob = ac_rec;
		this.gotController = true;
		this.internalConfig = conf;
	}
	
	public LpgpClient(Socket con) throws ClientAlreadyReceived{
		super(con);
	}
	
	public LpgpClient(){
		// empty
	}
	
	public void setController(ClientsController controller) throws ControllerAlreadyLoad{
		if(this.gotController) throw new ControllerAlreadyLoad();
		this.controller = controller;
		this.gotController = true;
	}
	
	@Nullable public ClientsController getController(){ return this.controller;}
	
	public boolean haveController(){ return this.gotController; }
	
	@NotNull public static String readFromReceiver(@NotNull  byte[] dataReceived){
		StringBuilder builder = new StringBuilder();
		for(byte chr : dataReceived){
			if(chr != 0) builder.append((char)chr);
		}
		return builder.toString();
	}
	
	public void authenticateClient() throws ControllerNotFound, NoSuchClient, IOException{
		if(!this.gotController) throw new ControllerNotFound();
		if(!this.gotClient) throw new NoSuchClient();
		try{
			PrintWriter sender = new PrintWriter(this.client.getOutputStream(), true);
			DataInputStream receiver = new DataInputStream(this.client.getInputStream());
			byte[] defaultCache = new byte[1024];
			sender.println(LpgpServer.HANDSHAKE);
			System.out.println("Sent HS");
			// sender.close();
			int i = receiver.read(defaultCache);
			String maskContent = readFromReceiver(defaultCache);
			this.validClient = this.controller.authClientMask(maskContent);
			MaskedData contentPure = new MaskedData(maskContent);
			boolean isRoot = this.controller.isClientRoot(maskContent);
			if(this.validClient){
				String ac_name = isRoot ? this.InternalConfig.getRootClient()[0] : this.InternalConfig.getNormalClient()[0];
				String ac_pass = isRoot ? this.InternalConfig.getRootClient()[1] : this.InternalConfig.getNormalClient()[1];
				sender.println(ac_name);
				sender.println(ac_pass);
			}
			else sender.println("Invalid client: ");
			System.out.println("Invalid");
			this.ac_recorder_ob.addAccessRecord(contentPure.getParsedPure().getInt("Client"), this.validClient);
			this.client.close();
			// validation done
		}
		catch(Exception e){
			this.validClient = false;
			new PrintStream(this.client.getOutputStream(), true).println("Invalid client: ");
		}
	}
	
	@Override
	public void run(){
		try{ authenticateClient();}
		catch(Exception e){ e.printStackTrace();}
	}
	
}
