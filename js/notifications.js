// Basic code to launch specific notifications

function notify(msg = "", content = "", lvl){
    /**
    Notifies the user with a message and a standard level
    The levels are:
    0 - Cookie notification
    1 - simple info notification
    2 - warning
    3 - error
    4 - fatal error (maybe'll be replaced by a redirect to a error page)
     */
    var uni_break = document.createElement("br");

    if(lvl == 0){
        // ignores the msg param
        var notification_container = document.createElement("div");
        var notification_title = document.createElement("h1");
        var notification_text = document.createElement("p");
        var notification_accept_bt = document.createElement("buttom");
        var notification_link_eula = document.createElement("a");

        // content configurations
        notification_title.innerText = "Our website uses cookies to improve user experience";
        notification_text.innerText = "Please agree with them to have a full experience with our products";
        notification_link_eula.innerText = "If you have any questions, see our privacy policy";
        notification_link_eula.href = "privacy_policy.html";
        notification_accept_bt.innerText = "Agree";

        // button function
        notification_accept_bt.onclick = function(){
            notification_container.classList.add("notification-min-out");
            notification_container.classList.remove("notification-min");
            setTimeout(function () {
                notification_container.remove();
            }, 120);
        }

        // classes configurations
        notification_container.id = "cookie-notification";
        notification_container.classList.add("notification-min");
        notification_container.classList.add("notification-cookie");
        notification_text.classList.add("notification-content");
        notification_title.classList.add("notification-title");
        notification_link_eula.classList.add("notification-link");
        notification_accept_bt.classList.add("notification-bt");
        notification_accept_bt.classList.add("btn");
        notification_accept_bt.classList.add("btn-success");
        notification_accept_bt.classList.add("btn-lg");

        // assembles
        notification_container.appendChild(notification_title);
        notification_container.appendChild(notification_text);
        notification_container.appendChild(notification_link_eula);
        notification_container.appendChild(uni_break);
        notification_container.appendChild(notification_accept_bt);

        document.body.appendChild(notification_container); // goes
    }
    else if(lvl < 5){
        // elements conditional data
        if(lvl == 1){
            var id = "info-notification";
            var cls = "notification-info";
        }
        else if(lvl == 2){
            var id = "war-notification";
            var cls = "notification-warning";
        }
        else{
            var id = "err-notification";
            var cls = "notification-error";
        }
        // elements
        var notification_container = document.createElement("div");
        var notification_title = document.createElement("h1");
        var notification_text = document.createElement("p");
        var notification_times_bt = document.createElement("button");

        // sets dismiss action
        notification_times_bt.onclick = function(){
            notification_container.classList.add("notification-min-out");
            notification_container.classList.remove("notification-min");
            setTimeout(function () {
                notification_container.remove();
            }, 120);
        }

        // sets contents
        notification_title.innerText = msg;
        notification_text.innerText = content;
        notification_times_bt.innerHTML = "&times;";

        // configures the classes
        notification_container.id = id;
        notification_container.classList.add("notification-min");
        notification_container.classList.add(cls);
        notification_text.classList.add("notification-content");
        notification_title.classList.add("notification-title");
        notification_times_bt.classList.add("btn");
        notification_times_bt.classList.add("btn-lg");
        notification_times_bt.classList.add("notification-dismiss");

        // assembles
        notification_container.appendChild(notification_times_bt);
        notification_container.appendChild(notification_title);
        notification_container.appendChild(notification_text);

        document.body.appendChild(notification_container); // goes
    }
    else{ alert("error"); }
}
