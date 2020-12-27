package View;
import org.jetbrains.annotations.*;

public class Colors{
	public static final String RESET = "\u001B[0m";
	
	// foreground colors
	public static final String _BLACK         = "\u001B[30m";
	public static final String _RED           = "\u001B[31m";
	public static final String _GREEN         = "\u001B[32m";
	public static final String _YELLOW        = "\u001B[33m";
	public static final String _BLUE          = "\u001B[34m";
	public static final String _PURPLE        = "\u001B[35m";
	public static final String _CYAN          = "\u001B[36m";
	public static final String _WHITE         = "\u001B[37m";
	public static final String _BRIGHT_BLACK  = "\u001B[90m";
	public static final String _BRIGHT_RED    = "\u001B[91m";
	public static final String _BRIGHT_GREEN  = "\u001B[92m";
	public static final String _BRIGHT_YELLOW = "\u001B[93m";
	public static final String _BRIGHT_BLUE   = "\u001B[94m";
	public static final String _BRIGHT_PURPLE = "\u001B[95m";
	public static final String _BRIGHT_CYAN   = "\u001B[96m";
	public static final String _BRIGHT_WHITE  = "\u001B[97m";
	
	// background colors
	public static final String B_BLACK         = "\u001B[40m";
	public static final String B_RED           = "\u001B[41m";
	public static final String B_GREEN         = "\u001B[42m";
	public static final String B_YELLOW        = "\u001B[43m";
	public static final String B_BLUE          = "\u001B[44m";
	public static final String B_PURPLE        = "\u001B[45m";
	public static final String B_CYAN          = "\u001B[46m";
	public static final String B_WHITE         = "\u001B[47m";
	public static final String B_BRIGHT_BLACK  = "\u001B[100m";
	public static final String B_BRIGHT_RED    = "\u001B[101m";
	public static final String B_BRIGHT_GREEN  = "\u001B[102m";
	public static final String B_BRIGHT_YELLOW = "\u001B[103m";
	public static final String B_BRIGHT_BLUE   = "\u001B[104m";
	public static final String B_BRIGHT_PURPLE = "\u001B[105m";
	public static final String B_BRIGHT_CYAN   = "\u001B[106m";
	public static final String B_BRIGHT_WHITE  = "\u001B[107m";
	
	@NotNull public static String generateErrorMessage(@NotNull  String messageRaw){
		return RESET  + _BRIGHT_WHITE +  B_BRIGHT_RED + "ERROR:" + RESET + _BRIGHT_RED + " " +  messageRaw + RESET;
	}
	
	@NotNull public static String generateLogo(){
		return " _                                 _   _     \n" +
				"| |_ __   __ _ _ __     __ _ _   _| |_| |__  \n" +
				"| | '_ \\ / _` | '_ \\   / _` | | | | __| '_ \\ \n" +
				"| | |_) | (_| | |_) | | (_| | |_| | |_| | | |\n" +
				"|_| .__/ \\__, | .__/   \\__,_|\\__,_|\\__|_| |_|\n" +
				"  |_|    |___/|_|    ";
	}
	
	public static void main(String[] args){
		// debug
		System.out.println(Colors.generateLogo());
	}
}