// this file contains the main methods that generate the cards of signatures/clients

/**
Generates a card of a signature using the data received by JSON and
dispose the card at a specified element
@param data The JSON with the signature data
@param dispose The ID of the element that will receive the card
@return void
*/
function genSignatureCard(data, dispose){
    var container = document.createElement("div");
    var contentDiv = document.createElement("div");
    var header = document.createElement("div");
    var signatureTitle = document.createElement("h1");
    var signatureSub = document.createElement("h3");
    var body = document.createElement("div");
    var propLink = document.createElement("a");
    var changelogDispose = document.createElement("div");
    var footerDiv = document.createElement("div");
    var configBtn = document.createElement("button");
    var configIcon = document.createElement("span");


    container.classList.add("card");
    container.classList.add("sig-card");

    contentDiv.classList.add("card-content");

    header.classList.add("card-header");
    signatureTitle.classList.add("card-title");
    signatureTitle.innerText = "#" + data["cd_signature"];

    signatureSub.innerText = "Created in: " + data["dt_creation"];
    signatureSub.classList.add("class-subtitle");

    header.appendChild(signatureTitle);
    header.appendChild(signatureSub);

    body.classList.add("card-body");

    propLink.href = "proprietary.php?id=" + btoa(data["id_proprietary"]);
    propLink.innerText = "Proprietary (click to visit)";

    changelogDispose.classList.add("changelog-card");
    body.appendChild(propLink);
    body.appendChild(changelogDispose);

    footerDiv.classList.add("card-footer");
    configIcon.classList.add("fas");
    configIcon.classList.add("fas-cog");
    configBtn.classList.add("btn");
    configBtn.classList.add("btn-lg");
    configBtn.classList.add("btn-secondary");
    configBtn.appendChild(configIcon);
    configBtn.innerText = "Configurations";

    footerDiv.appendChild(configBtn);
    contentDiv.appendChild(header);
    contentDiv.appendChild(body);
    contentDiv.appendChild(footerDiv);

    container.appendChild(contentDiv);

    document.getElementById(dispose).appendChild(container);
}
