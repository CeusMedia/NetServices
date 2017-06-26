UI = typeof UI === 'undefined' ? {} : UI;

UI.ENSClient = {

	start: null,
	client: null,

	init: function(uri,options){
		this.client = new ENSClient(uri,options);
	},

	startClock: function(){
		this.start = new Date().getTime();
	},
	stopClock: function(){
		$("#time").html(new Date().getTime() - this.start);
	},
	getRegExp: function(string){
		var delimiter = string.substr(0,1);
		var parts = string.split(delimiter);
		parts.shift();
		var pattern = parts.shift();
		var modifiers = parts.shift();;
		return new RegExp(pattern,modifiers)
	},

	renderServiceForm: function(service){
		$('#parameters > div').remove();
		$('#parameters').append('<div></div>');
		var list = $('#parameters > div');
		data	= this.client.read('disclosure/getServiceParameters',{path:'demo', service:service});
		$("#def").html(JSON.stringify(data).formatJSON());
		var field;
		for(var param in data){
			param = $.extend(data[param],{name:param});
//			console.log(param);
			label	= param.name;
			if(param.title)
				label	= $('<acronym></acronym>').attr('title',param.title).html(label);
			label = $('<label/>').attr('for','param-'+param.name).html(label);

			field = $('<input/>').attr({type: 'text', name: param.name, id: 'param-'+param.name}).addClass('mandatory');
			field.bind('keyup',{data:param},function(event){
				var data = event.data.data;
				if(data.preg){
					var regExp = UI.ENSClient.getRegExp(data.preg);
					if(!regExp.test($(this).val()))
						$(this).addClass('invalid');
					else
						$(this).removeClass('invalid');
				}
			})
			item  = $('<div></div>').append(label).append(field.trigger('keyup'));
			list.append(item);
		}
		$("#form button").bind('click',function(){
			UI.ENSClient.startClock();
			var param = {};
			$("#form :input").each(function(){
				param[$(this).attr('name')]	= $(this).val();
			});
			var data	= UI.ENSClient.client.read('demo/'+service,param,function(data){
				$("#result").html(JSON.stringify(data).formatJSON());
			});
			UI.ENSClient.stopClock()
		});
	},

	renderServiceList: function(){
		var data	= UI.ENSClient.client.read('disclosure/getServicesFromPath',{path:'demo'});
	//	console.log(JSON.stringify(data).formatJSON());
		var list	= $('<ul></ul>');
		for(var i in data)
			list.append('<li><a href="./?service='+data[i]+'">'+data[i]+'</a></li>');
		$("#services").html(list);
	}
};
