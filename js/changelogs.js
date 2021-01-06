// file that contains the main methods to work with the
// changelogs of signatures and clients
const m_signature = 1;
const m_client    = 0;
var swp_changelogs = {};
var swap_full = false;

/**
Uses AJAX method to get the changelogs of a specific client/signature
using a reference (the primary key)
@param reference The primary key reference of the client/signature
@param clientSignatureMode There'll be changelogs of a client (0) or a signature (1)
@return JSON object with the data and changelogs
*/
function getChangelogs(reference, clientSignatureMode = 0){
    $.post({
        url: "ajx_changelogs.php",
        data: {"ref": reference, "mode": clientSignatureMode},
        dataType: "json",
        success: function(resp){ swp_changelogs = resp},
        error: function(error){ console.error(error); }
    });
}

function genChangelogTree(changelog, mode = m_signature){
    
}
