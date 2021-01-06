// there's some uses of the http://localhost/ if you don't want it then just change't

function include(src){
    var head = document.querySelector("head");
    var script = document.createElement("script");
    script.src = src;
    head.appendChild(script);
}

function generateChart(chartData, canvas){
    include("https://cdn.jsdelivr.net/npm/chart.js@2.8.0");
    var idCanvas = document.getElementById(canvas);
    var chart = new Chart(idCanvas, chartData);
}

/**
 * Resets the values at the localStorage.
 * The values are:
 *    logged-user => if there's a user logged ("true" or "false")
 *    user_mode   => if the logged user is a proprietary or a normal user ("null", 1, 0). null if there's no user logged. 0 if is a normal, 1 if is a proprietary;
 *    checked     => if the user email was checked ("true", "false", "null")
 *    dark-room   => if the screen will use the dark mode or the light mode (true, false)
 *    switcher_dk => if the switcher of the dark room will be dark or not
 */
function resetVals(){
    localStorage.setItem("logged-user", "false");
    localStorage.setItem("user_mode", "null");
    localStorage.setItem("checked", "null");
    localStorage.setItem("user-icon", "null");
}

function clsLoginOpts(){
    try{
        document.querySelectorAll("#item1 .options-login .opt-login").remove();
    }
    catch(e){ } // do nothing
}

function clsSignOpts(){
    try{
        for(var i in document.querySelectorAll("#item2 .si-opts .opt-signature")){
            document.querySelector("#item2 .si-opts").removeChild(i);
        }
    }
    catch(ex){}  // do nothing;
}

function getChartHead(){
    var mainObj = document.querySelector("head");
    var scriptObj = document.createElement("script");
    scriptObj.src = '<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>';
    mainObj.appendChild(scriptObj);
}

function setAccountOpts(ext_fls = false){
    /**
     *
     */
    clsLoginOpts();
    rmClientDrop();
    var local_opts = document.querySelector(".login-dropdown .dropdown-menu");
    var gbl_opts = document.querySelector(".header");
    var linkHome = document.createElement("a");

    linkHome.classList.add("home-link");
    linkHome.id = "home-link";
    linkHome.href = "https://" + window.location.hostname + "/";

    gbl_opts.appendChild(linkHome);

    if(localStorage.getItem("logged-user") == "true"){
        var account_opt = document.createElement("a");
        var logoff_opt = document.createElement("a");
        var config_opt = document.createElement("a");

        config_opt.href = "https://" + window.location.hostname + "/ch_my_data.php";
        logoff_opt.href = "https://" + window.location.hostname + "/logoff.php";
        account_opt.href = "https://"+ window.location.hostname + "/my_account.php";

        // classes
        config_opt.classList.add("dropdown-item");
        logoff_opt.classList.add("dropdown-item");
        account_opt.classList.add("dropdown-item");

        config_opt.innerText = "Configurations";
        logoff_opt.innerText = "Logoff";
        account_opt.innerText = "My account";

        local_opts.appendChild(config_opt);
        local_opts.appendChild(logoff_opt);
        local_opts.appendChild(account_opt);
        setClientsDrop();
        var img = document.createElement("img");
        img.width = 30;
        img.height = 30;
        var local_opt_btn = document.querySelector("#account-opts");
        img.src = getLinkedUserIcon();
        img.classList.add("user-icon");
        document.querySelector("#account-opts span").remove();
        local_opt_btn.appendChild(img);
    }
    else{
        var login_opt = document.createElement("a");
        var ct_accopt = document.createElement("a");

        login_opt.href = "https://"+  window.location.hostname + "/login_frm.php";
        ct_accopt.href = "https://"+  window.location.hostname + "/create_account_frm.php";
        login_opt.classList.add("dropdown-item");
        ct_accopt.classList.add("dropdown-item");
        login_opt.innerText = "Make login";
        ct_accopt.innerText = "Create Account";

        local_opts.appendChild(login_opt);
        local_opts.appendChild(ct_accopt);

        var err = false;
        try{
            document.querySelector(".user-icon").remove();
            document.querySelector(".nm-tmp").remove();
        }
        catch(error){
            console.log("There's no image to remove!");
            err = true;
        }
        if(!err){
            var sp = document.createElement("span");
            sp.innerHTML = "Account";
            document.querySelector("#account-opts").appendChild(sp);
        }
    }
    setBtnSolutions(".header");
}

function rmClientDrop(){
    try{
        document.querySelector("#my-clients").remove();
        document.querySelector("#add-client").remove();
        document.querySelector("#check-client").remove();
    }
    catch($e) {}
}

/**
 *
 */
function setSignatureOpts(){
    clsSignOpts();
    var local_opts = document.querySelector(".signatures-dropdown .dropdown-menu");
    if(localStorage.getItem("user_mode") == "prop"){
        // is a proprietary account
        var che_sig = document.createElement("a");
        var my_sign = document.createElement("a");

        my_sign.innerText = "My Signatures";
        che_sig.innerText = "Check a Signature";
        my_sign.href = "https://" + window.location.hostname +"/my_signatures.php";
        che_sig.href = "https://" + window.location.hostname +"/check_signature.php";
        my_sign.classList.add("dropdown-item");
        che_sig.classList.add("dropdown-item");


        local_opts.appendChild(my_sign);
        local_opts.appendChild(che_sig);
    }
    else if(localStorage.getItem("user_mode") == "normie"){
        var chk_signature = document.createElement("a");

        chk_signature.innerText = "Check a Signature";
        chk_signature.href = "https://" + window.location.hostname + "/check_signature.php";
        chk_signature.classList.add("dropdown-item");

        local_opts.appendChild(chk_signature);
    }
    else{
        var login_need = document.createElement("a");
        login_need.innerText = "Make login for check a signature";
        login_need.href = "https://" + window.location.hostname +"/login_frm.php";
        login_need.classList.add("dropdown-item");
        local_opts.appendChild(login_need);
        delete(login_need);
    }
}

function setClientsDrop(){
    if(localStorage.getItem("user_mode") == "prop"){
        var localTo = document.querySelector(".signatures-dropdown .dropdown-menu");
        var optAdd = document.createElement("a");
        var optMy = document.createElement("a");
        var optCh = document.createElement("a");

        optAdd.classList.add("dropdown-item");
        optMy.classList.add("dropdown-item");
        optCh.classList.add("dropdown-item");

        optAdd.href = "https://" + window.location.hostname + "/create-client.php";
        optAdd.innerText = "Create a Client";
        optAdd.id = "add-client";

        optMy.href = "https://" + window.location.hostname + "/my-clients.php";
        optMy.innerText = "My clients";
        optMy.id = "my-clients";

        optCh.href = "https://" + window.location.hostname + "/check-client.php";
        optCh.innerText = "Check client authentication";
        optCh.id = "check-client";

        localTo.appendChild(optAdd);
        localTo.appendChild(optMy);
        localTo.appendChild(optCh);
    }
    else{
        var localTo = document.querySelector(".login-dropdown .dropdown-menu");
        var optCh = document.createElement("a");

        optCh.href = "https://" + window.location.hostname + "/check-client.php";
        optCh.classList.add("dropdown-item");
        optCh.innerText = "Check client authentication";
        optCh.id = "check-client";

        localTo.appendChild(optCh);
    }
}

/**
 *
 * @param {*} message
 */
function showError(message){
    /**
     *
     */
    var error_lbs = document.querySelector(".error-lb");
    error_lbs.innerHTML = message;
    error_lbs.setAttribute("style", "visibility: visible;");
}

function hideError(){
    var error_lbs = document.querySelector(".error-lb");
    error_lbs.setAttribute("style", "visibility: hidden;");
}

function getLinkedUserIcon(){
    var ls  = localStorage.getItem("user-icon");
    return "https://" + window.location.hostname + "/" + ls;
}

function setBtnSolutions(locale){
    var localeNode = document.querySelector(locale);
    var link = document.createElement("a");
    link.href = "https://" + window.location.hostname + "/solutions.html";
    link.id = "solutions";
    link.setAttribute("role", "button");
    link.classList.add("btn");
    link.classList.add("default-btn-header");
    link.classList.add("btn-lg");
    link.innerText = "Solutions";
    localeNode.appendChild(link);
}

function parseLogin(){
    $.post({
        url: "ajx_logged_request.php",
        data: "getJSON=t",
        success: function(json){
            var brute = $.parseJSON(json);
            if(brute["Logged"]){
                localStorage.setItem("logged-user", "true");
                localStorage.setItem("user_mode", brute["Mode"] == "1" ? "prop" : "normie");
                localStorage.setItem("checked", brute["Checked"]);
                localStorage.setItem("user-icon", brute["ImgUrlPath"]);
                localStorage.setItem('user', brute["Username"]);
                localStorage.setItem("email", brute["Email"]);
            }
            else{
                localStorage.setItem("logged-user", "false");
                localStorage.setItem("user_mode", null);
                localStorage.setItem("checked", null);
                localStorage.setItem("user-icon", null);
                localStorage.setItem('user', null);
                localStorage.setItem("email", null);
            }

        },
        error: function(xhr, status, error){ console.log(error); }
    });
}
