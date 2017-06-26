
$(document).ready(function(){
	var request	= (window.location.search||'').deparam();
	UI.ENSClient.init('../DisclosureDemo/',{});
	UI.ENSClient.renderServiceList(request);
	if(request.service){
		UI.ENSClient.renderServiceForm(request.service);
	}
});
