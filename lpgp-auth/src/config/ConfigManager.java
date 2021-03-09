package config;

import java.io.IOException;
import java.io.FileReader;
import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.FileWriter;
import org.json.JSONObject;
import org.json.JSONArray;
import org.jetbrains.annotations.*;

public class ConfigManager{
	
	public final static String CONFIG_DFT = "src/config/sconfig_schema.json";
	
	private String configLoaded = null;
	private boolean gotConfig = false;
	private String cachedContent = null;
	private boolean changedCache = false;
	
	public static class ConfigLoadingError extends Exception{
		
		public ConfigLoadingError(String config, String msg) {
			super("Can't load the configurations file " + config + ". ERROR: " + msg);
		}
	}
	
	public static class ConfigNotLoadedError extends Exception{
		
		public ConfigNotLoadedError(){ super("There's no configurations file loaded yet!");}
	}
	
	public static class ConfigAlreadyLoadedError extends Exception{
		
		public ConfigAlreadyLoadedError(){ super("There's a configurations file loaded already!");}
	}
	
	@Nullable public String getConfigLoaded(){ return this.configLoaded;}
	
	@Nullable public String getCache(){return this.cachedContent;}
	
	public boolean isGotConfig(){ return this.gotConfig; }
	
	@Nullable public JSONObject getParsedCache(){ return this.gotConfig ? new JSONObject(this.cachedContent) : null; }
	
	public boolean changedCache(){ return this.changedCache; }
	
	public void setCContent(@NotNull  String newContent){
		this.cachedContent = newContent;
		this.changedCache = true;
	}
	
	public void setCContent(@NotNull  JSONObject jsonContent){
		this.cachedContent = jsonContent.toString();
		this.changedCache = true;
	}
	
	public void loadConfig(@NotNull  String configPath) throws ConfigAlreadyLoadedError, ConfigLoadingError{
		if(this.gotConfig) throw new ConfigAlreadyLoadedError();
		StringBuilder localCache;
		try{
			BufferedReader bfr = new BufferedReader(new FileReader(configPath));
			localCache = new StringBuilder();
			String line;
			while((line = bfr.readLine()) != null) localCache.append(line);
		}
		catch(Exception e){
			throw new ConfigLoadingError(configPath, e.getMessage());
		}
		if(localCache == null) throw new ConfigLoadingError(configPath, "UNIDENTIFIED ERROR");
		this.cachedContent = localCache.toString();
		this.configLoaded = configPath;
		this.gotConfig = true;
	}
	
	public void commitChanges() throws IOException, ConfigNotLoadedError{
		if(!this.gotConfig) throw new ConfigNotLoadedError();
		BufferedWriter bfw = new BufferedWriter(new FileWriter(this.configLoaded));
		if(this.changedCache) bfw.write(this.cachedContent);
		bfw.close();
		this.changedCache = false;
	}
	
	public void closeConfig() throws ConfigNotLoadedError, IOException{
		// if(!this.gotConfig) throw new ConfigNotLoadedError();
		if(this.changedCache) this.commitChanges();
		this.cachedContent = null;
		this.configLoaded = null;
		this.gotConfig = false;
	}
	
	
}
