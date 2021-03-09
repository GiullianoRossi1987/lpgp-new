package control;

import org.json.*;
import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import org.jetbrains.annotations.*;
import java.lang.Exception;


public class BaseController{

    protected String controlRecorder = null;
    protected JSONObject controlMain = null;
    protected boolean gotControl = false;

    public static class ControlRecorderNotLoaded extends Exception{

        public ControlRecorderNotLoaded(){ super("There's no control recorder file loaded!");}
    }

    public static class ControlRecorderAlreadyLoaded extends Exception{

        public ControlRecorderAlreadyLoaded(){ super("There's a control recorder file already loaded!");}
    }

    public void loadRecorder(String recorderPath) throws ControlRecorderAlreadyLoaded, IOException{
        if(this.gotControl) throw new ControlRecorderAlreadyLoaded();
        FileReader reader = new FileReader(recorderPath);
        BufferedReader rfd = new BufferedReader(reader);
        StringBuilder content = new StringBuilder();
        String pos;
        while((pos = rfd.readLine()) != null){
            content.append(pos);
        }

        this.controlMain = new JSONObject(content.toString());
        this.controlRecorder = recorderPath;
        this.gotControl = true;
        reader.close();
        rfd.close();
    }

    public void unloadRecorder() throws ControlRecorderNotLoaded{
        if(!this.gotControl) throw new ControlRecorderNotLoaded();
        this.controlRecorder = null;
        this.controlMain = null;
        this.gotControl = false;
    }

    public BaseController(String recorder) throws ControlRecorderAlreadyLoaded, IOException{
        this.loadRecorder(recorder);
    }

    public BaseController(){
        this.controlMain = null;
        this.controlRecorder = null;
        this.gotControl = false;
    }

    public String getRecorder() throws ControlRecorderNotLoaded{
        if(!this.gotControl) throw new ControlRecorderNotLoaded();
        else return this.controlRecorder;
    }

    public JSONObject getRecords() throws ControlRecorderNotLoaded{
        if(!this.gotControl) throw new ControlRecorderNotLoaded();
        else return this.controlMain;
    }

    public boolean haveRecorder(){ return this.gotControl; }

}