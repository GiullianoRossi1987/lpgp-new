
function loadJquery(internalPath){
    let linkB_E = document.createElement("link");
    let scriptB_E = document.createElement("script");
    let scriptJ_E = document.createElement("script");

    if(internalPath){
        linkB_E.href = "../jquery/lib/bootstrap/css/bootstrap.css";
        scriptB_E.src = "../jquery/lib/bootstrap/js/bootstrap.js";
        scriptJ_E.src = "../jquery/lib/jquery-3.4.1.min.js";
    }
    else{
        linkB_E.href = "jquery/lib/bootstrap/css/bootstrap.css";
        scriptB_E.src = "jquery/lib/bootstrap/js/bootstrap.js";
        scriptJ_E.src = "jquery/lib/jquery-3.4.1.min.js";
    }
    document.querySelector("head").appendChild(linkB_E);
    document.querySelector("head").appendChild(scriptB_E);
    document.querySelector("head").appendChild(scriptJ_E);
}


function previewImageTmp(internalPath, imgInput){
    var source = null;
    let imgData = new FormData();
    imgData.append("img-auto-load", $(imgInput)[0].files[0]);
    $.post({
        url: internalPath ? "../cgi-actions/ajx_img_viewer.php" : "cgi-actions/ajx_img_viewer.php",
        data: imgData,
        processData: false,
        contentType: false,
        success: function(response){ source = response; }
    });
    return source;
}


function requestChart(client, mode, chartDisposeId){
    var data = client !== null && client != 0 ? "client="+parseInt(client)+"&mode="+parseInt(mode) : "mode="+parseInt(mode);
    $.post({
        url: "ajx_chart_view.php",
        data: data,
        success: function(response){ eval(response.replace("<script>", "").replace("</script>", "")); },
        error: function(xhr, status, error){ console.error(error); }
    });
}


/**
 * That method sends the search content of the main-query.php to the ajax
 * interpreter ajx_query_main.php .
 * @param string scope The scope of the search, if it's in all the whole server ('all')
 *                     or just in the account ('me'); [the account search is available
 *                     only to the proprietaries]
 * @param string needle The needle name to search
 * @param int type The type of the result => 0 : For all; 1: Only Accounts; 2: Only
 *                 Proprietaries Accounts; 3: Only Normal Accounts; 4: Only clients
 * @param disposeResults The ID of the location to dispose the results.
 */
function requestQuery(needle, scope, mode, disposeResults){
    $.post({
        url: "ajx_query_main.php",
        data: "scope="+scope+"&needle="+needle+"&mode="+parseInt(mode),
        dataType: 'text',
        success: function(response){ $(disposeResults).html(response); },
        error: function(xhr, status, error){ console.error(error); }
    });
}


/**
 * That method works sending a specific request for filtering the signatures
 * or clients.
 * @param {int} type The type of the items to search (0 = signatures, 1 = clients)
 * @param {int} mode The mode of the filter:
 *              * 0 => None
 *              * 11 => Sort the newest signatures
 *              * 12 => Sort the oldest signatures
 *              * 13 => Only MD5 signatures
 *              * 14 => Only SHA1 signatures
 *              * 15 => Only SHA256 signatures
 *              * 21 => A-Z Clients
 *              * 22 => Z-A Clients
 * @param {str} dispose The id of the element to dispose the results
 */
function requestFilter(type, mode, dispose){
    let dp_Scope = type == 0 ? "s" : "c";
    $.post({
        url: "ajx_filter.php",
        data: "scope="+dp_Scope+"&sortType="+mode,
        dataType: 'text',
        success: function(response){ $(dispose).html(response); },
        error: function(xhr, status, error){ console.error(error); }
    });
}

function genSessionErrorModal(link_replacement){
    var mainModal = document.createElement("div");
    var modalDialog = document.createElement("div");
    var modalContent = document.createElement("div");
    var modalHeader = document.createElement("div");
    var modalTitle = document.createElement("h1");
    var modalBody = document.createElement("div");
    var modalText = document.createElement("p");
    var anchorMain = document.createElement("a");

    mainModal.classList.add("modal");
    mainModal.classList.add("fade");
    mainModal.classList.add("session-error-m");
    mainModal.setAttribute("data-backdrop", "static");
    mainModal.setAttribute("data-keyboard", false);
    mainModal.setAttribute("tabindex", -1);
    mainModal.setAttribute("aria-hidden", false);
    mainModal.id = "session-error-m";

    modalDialog.classList.add("modal-dialog");

    modalContent.classList.add("modal-content");


    modalHeader.classList.add("modal-header");
    modalTitle.classList.add("modal-title");
    modalTitle.innerText = "Session error, sorry";
    modalTitle.id = "session-error-m-title";

    modalBody.classList.add("modal-body");
    modalText.innerText = "Please make the login again";

    anchorMain.classList.add("btn");
    anchorMain.classList.add("btn-lg");
    anchorMain.classList.add("btn-secondary");
    anchorMain.role = "button";
    anchorMain.onclick = function(){
        window.location.replace(link_replacement);
    };
    anchorMain.innerText = "Back to the login";

    modalBody.appendChild(modalText);
    modalBody.appendChild(anchorMain);
    modalHeader.appendChild(modalTitle);
    modalContent.appendChild(modalHeader);
    modalContent.appendChild(modalBody);
    modalDialog.appendChild(modalContent);
    mainModal.appendChild(modalDialog);

    // requires the JQUERY
    $(mainModal).modal("show");
    $(mainModal).on("hidden.bs.modal", function(e){ window.location.replace(link_replacement); });
}
