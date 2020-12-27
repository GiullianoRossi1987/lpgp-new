// coding = utf-8

function include(src){
	var head = document.querySelector("head");
	var srcp = document.createElement("script");
	srcp.src = src;
	head.appendChild(srcp);
}

// main types of charts to be generateds

function generateChart(canvasId, data){
	var canvas = document.getElementById(canvasId);
	var charter = new Chart(canvas, data);
}