// coding = utf-8

function loadSearchButton(){
    let mainBtn = document.createElement("a");
    let spanI = document.createElement("span");
    let iElem = document.createElement("i");

    iElem.classList.add("fas");
    iElem.classList.add("fa-search");

    spanI.appendChild(iElem);

    mainBtn.id = "qr-btn";
    mainBtn.classList.add("btn");
    mainBtn.classList.add("btn-lg");
    mainBtn.appendChild(spanI);
    mainBtn.href = "https://" + window.location.hostname + "/cgi-actions/main-query.php";
    mainBtn.role = "button";

    document.querySelector(".header-container .header").appendChild(mainBtn);
}

function loadJSLibs(){

};

function startup(){

};
