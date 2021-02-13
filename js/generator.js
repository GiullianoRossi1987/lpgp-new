// this file contains the main methods that generate the cards of signatures/clients

function checkUser(id, mode = 0){
    var tt = undefined;
    if(mode == 0){
        // normal user
        tt = $.post({
            url: "ajx_users.php",
            data: {check: id},
            async: false,
            dataType: "json",
            success: function(resp){ return resp; },
            error: function(error){ console.error(error); }
        }).responseJSON;
    }
    else{
        // proprietary
        tt = $.post({
            url: "ajx_prop.php",
            data: {check: id},
            async: false,
            dataType: "json",
            success: function(resp){ return resp; },
            error: function(error){ console.error(error); }
        }).responseJSON;
    }
    return tt["exists"];
}

/**
Generates a card of a signature using the data received by JSON and
dispose the card at a specified element
@param data The JSON with the signature data
@param dispose The ID of the element that will receive the card
@return void
*/
function genSignatureCard(data, dispose){
    var container        = document.createElement("div");
    var contentDiv       = document.createElement("div");
    var header           = document.createElement("div");
    var signatureTitle   = document.createElement("h1");
    var signatureSub     = document.createElement("h3");
    var body             = document.createElement("div");
    var propLink         = document.createElement("a");
    var changelogDispose = document.createElement("div");
    var footerDiv        = document.createElement("div");
    var configBtn        = document.createElement("a");
    var configIcon       = document.createElement("span");
    var downloadBtn      = document.createElement("a");
    var downloadIcon     = document.createElement("span");


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
    configIcon.classList.add("fa-cog");
    configBtn.classList.add("btn");
    configBtn.classList.add("btn-lg");
    configBtn.classList.add("btn-secondary");
    configBtn.innerText = "Configurations";
    configBtn.role = "button";
    configBtn.href = "ch_signature_data.php?sig_id=" + btoa(data["cd_signature"]);
    configBtn.appendChild(configIcon);

    downloadIcon.classList.add("fas");
    downloadIcon.classList.add("fa-file-download");
    downloadBtn.classList.add("btn");
    downloadBtn.classList.add("btn-lg");
    downloadBtn.classList.add("btn-secondary");
    downloadBtn.role = "button";
    downloadBtn.innerText = "Download Signature File";
    downloadBtn.href = "get_my_signature.php?id=" + btoa(data["cd_signature"]);
    downloadBtn.appendChild(downloadIcon);

    footerDiv.appendChild(configBtn);
    footerDiv.appendChild(downloadBtn);
    contentDiv.appendChild(header);
    contentDiv.appendChild(body);
    contentDiv.appendChild(footerDiv);

    container.appendChild(contentDiv);

    document.getElementById(dispose).appendChild(container);
}

/**
 * Generates the signature add button, this button is used at the profile page
 * on the final of the signatures section, to be able to add an brand new signature
 * @param dispose The id of the section to put the button
 */
function genSignatureAdd(dispose){
    var button = document.createElement("a");
    button.classList.add("btn");
    button.classList.add("btn-block");
    button.classList.add("btn-success");
    button.role = "button";
    button.innerText = "Create a new signature";
    button.href = "create_signature.php";
    document.getElementById(dispose).appendChild(button);
}

function genClientCard(data, dispose){
    var container        = document.createElement("div");
    var containerContent = document.createElement("div");
    var header           = document.createElement("div");
    var body             = document.createElement("div");
    var footer           = document.createElement("div");
    var chartBtn         = document.createElement("a"); // body
    var downloadBtn      = document.createElement("a"); // body
    var configBtn        = document.createElement("a"); // body
    var titleCC          = document.createElement("h1"); // header
    var subTitle         = document.createElement("h3"); // header
    var accTitle         = document.createElement("h5"); // footer

    container.classList.add("card");
    container.classList.add("client-card");

    containerContent.classList.add("card-content");

    header.classList.add("card-header");
    body.classList.add("card-body");
    footer.classList.add("card-footer");

    titleCC.classList.add("card-title");
    titleCC.innerText = "Client " + data["nm_client"];
    subTitle.classList.add("card-subtitle");
    subTitle.classList.add("mb-2");
    subTitle.innerText = "#" + data["cd_client"];
    header.appendChild(titleCC);
    header.appendChild(subTitle);

    chartBtn.classList.add("btn");
    chartBtn.classList.add("btn-lg");
    chartBtn.classList.add("btn-secondary");
    chartBtn.role = "button";
    chartBtn.href = "client-accesses.php?client=" + btoa(data["cd_client"]);
    chartBtn.innerHTML = "<span class=\"fas fa-chart-bar\"></span>Client acesses";

    downloadBtn.classList.add("btn");
    downloadBtn.classList.add("btn-lg");
    downloadBtn.classList.add("btn-secondary");
    downloadBtn.role = "button";
    downloadBtn.href = "client-data.php?client=" + btoa(data["cd_client"]);
    downloadBtn.innerHTML = "<span class=\"fas fa-box\"></span>Download the Client data";

    configBtn.classList.add("btn");
    configBtn.classList.add("btn-lg");
    configBtn.classList.add("btn-secondary");
    configBtn.role = "button";
    configBtn.href = "ch_client.php?client=" + btoa(data["cd_client"]);
    configBtn.innerHTML = "<span class=\"fas fa-cog\"></span>Configurations";

    body.appendChild(chartBtn);
    body.appendChild(downloadBtn);
    body.appendChild(configBtn);

    accTitle.innerText = "Accesses: " + data["acesses"];
    footer.appendChild(accTitle);

    containerContent.appendChild(header);
    containerContent.appendChild(body);
    containerContent.appendChild(footer);

    container.appendChild(containerContent);
    document.getElementById(dispose).appendChild(container);

    // TODO: finish the generator methods (and continue the changelogs developmnent);
}

function genClientAdd(dispose){
    var button = document.createElement("a");

    button.classList.add("btn");
    button.classList.add("btn-block");
    button.classList.add("btn-success");
    button.role = "button";
    button.innerText = "Create new Client";
    button.href = "create-client.php";

    document.getElementById(dispose).appendChild(button);
}

function genHistoryCard_p(data, dispose){
    var container = document.createElement("div");
    var containerContent = document.createElement("div");
    var header = document.createElement("div");
    var body = document.createElement("div");
    var footer = document.createElement("div");
    var signatureTitle = document.createElement("h2"); // header
    var debugButton = document.createElement("button"); // body
    var propLink = document.createElement("a"); // body
    var dtChecked = document.createElement("h4"); // footer

    if(data["vl_valid"] == 0) container.classList.add("hs-valid");
    else container.classList.add("hs-invalid");
    container.classList.add("card");

    containerContent.classList.add("card-content");
    header.classList.add("card-header");
    signatureTitle.classList.add("card-title");
    singatureTitle.innerText = "Signature #" + data["id_signature"];
    header.appendChild(signatureTitle);

    var sig_data = $.post({
        url: "ajx_signatures.php",
        data: {get: JSON.stringify({cd_signature: data["id_signature"]})},
        dataType: "json",
        async: false,
        success: function(data){ return data; },
        error: function(error){ alert(error); }
    }).responseJSON;



    var existsProp = checkUser(sig_data["id_proprietary"], 1);
    propLink.innerText = "Go to proprietary";
    if(existsProp) propLink.href = "proprietary.php?id=" + btoa(sig_data["id_proprietary"]);
    else{
        propLink.innerText += "(This proprietary don't exists anymore)";
        propLink.setAttribute("data-toggle", "tooltip");
        propLink.setAttribute("title", "This link is unvailable");
    }


    footer.classList.add("card-footer");
    dtChecked.innerText = "Checked signature at: " + data["dt_reg"];
    dtChecked.classList.add("text-muted");
    footer.appendChild(dtChecked);

    document.getElementById(dispose).appendChild()
}
