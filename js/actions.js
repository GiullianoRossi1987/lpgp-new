// coding = utf-8
// using namespace std

/**
 * That file have all the buttons and fields methods.
 */

const normalUsersReportTypes = ["Other", "Signature checking", "Client Checking", "My Account", "WebSite Design"];
const proprietariesReportTypes = ["Other", "Signature checking",
                                  "Client Checking", "My Account",
                                  "Signatures Management", "Clients Management",
                                  "Access Plot view", "WebSite Design"];

/**
 * Show a error in the page, using the .error-lb class and specifying the label ID
 * @param {*} id The specific label ID
 * @param {*} message The error message.
 */
function showError(id, message){
	var err_lb = document.getElementById(id);
	err_lb.innerText = message;
	err_lb.style += "visibility: visible;";
}

function hideError(id_err){
	var err = document.getElementById(id_err);
	err.style += "visibility: hidden;";
}


/**
 * Checks if there's a error with the login form page
 *
 * @param pas1_inp The reference of the password input tag on the document. Used the name.
 * @param pas2_inp The reference of the password confirmation input tag on the document. Used the name
 * @param nm_inp The reference of the username input tag on the document. Used the ID normally
 * @param err_id The reference of the error label of the page.
 */

function loginck(pas1_inp, pas2_inp, nm_inp, err_id = "#err-lb"){
	var pas1_i = document.getElementByName(pas1_inp);
	var pas2_i = document.getElementByName(pas2_inp);
	var nm_i   = document.getElementByName(nm_inp);

	if(pas1_i.value != pas2_i.value)
		showError(err_id, "The passwords don't match!");
	else if(lenght(nm_i.value) == 0)
		showError(err_id, "Please confirm your username");
	else
		hideError(err_id);

}


function showOpt(opt_id){
	var toShow = document.getElementById(opt_id);
	toShow.classList.add("showing-conopt");
}

function hideOpt(opt_id){
	var toHide = document.getElementById(opt_id);
	try{
		toHide.classList.remove("showing-conopt");
	}
	catch(errr){
		console.error(errr);
	}
}

function applyToA(){
	var all_a = document.getElementsByTagName("a");
	for(var item_i = 0; item_i < all_a.length; item_i++){
		all_a[item_i] = all_a[item_i].href.replace("https://www.lpgpofficial.com", "https://" + window.location.href);
		all_a[item_i] = all_a[item_i].href.replace("https://www.lpgpofficial.com", "https://" + window.location.href);
	}
}

function applyToForms(){
	var all_a = document.getElementsByTagName("form");
	for(var item_i = 0; item_i < all_a.length; item_i++){
		all_a[item_i] = all_a[item_i].action.replace("https://www.lpgpofficial.com", "https://" + window.location.href);
		all_a[item_i] = all_a[item_i].action.replace("https://www.lpgpofficial.com", "https://" + window.location.href);
	}
}

function notificate(msg, mode){
	var parent = document.createElement("div");
	var btnDismiss = document.createElement("button");
	var message = document.createElement("p");
	var dismissContainer = document.createElement("div");

	message.innerText = msg;

	btnDismiss.classList.add("btn");
	btnDismiss.innerHTML = "&times";
	btnDismiss.onclick = function(){ parent.remove(); }

	dismissContainer.classList.add("dismiss-container");
	dismissContainer.appendChild(btnDismiss);

	parent.appendChild(message);
	parent.appendChild(dismissContainer);
	parent.classList.add("notification");
	if(mode == 1){
		// error
		parent.classList.add("notification-warning");
	}
	else if(mode == 2){
		// success
		parent.classList.add("notification-success");
	}
	else{
		// normal
		parent.classList.add("notification-normal");
	}
	document.querySelector(".header-container .header #notification-container").appendChild(parent);
}

function typingEffect(msg, dispose){
    var posT = 0;
    var tmw = 200; // ms
    function typing(){
        document.getElementById(dispose).text = msg[posT]
    }
    for(i = 0; i < len(msg); i++){
        setTimeout(typing, tmw);
        posT = i;
    }
}
