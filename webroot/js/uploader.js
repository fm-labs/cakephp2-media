function Uploader() {
	
	this.settings = {};
	
	this.defaultSettings = {
		holder: '#file', // file input
		uploadUrl: 'upload.php',
		maxRetry: 3,
		autoProcessQueue: true,
		onInit: function (uploader) {},
		onAddToQueue: this.onAddToQueue, // function (file) {}
		onTransferComplete: this.onTransferComplete, // function (uploader, file, xhr, evt) {}
		onTransferProgress: this.onTransferProgress, // function (uploader, file, xhr, evt) {}
		onTransferFailure: this.onTransferFailure, // function (uploader, file, xhr, evt) {}
		onTransferAbort: this.onTransferAbort // function (uploader, file, xhr, evt) {}
	};
	
	this.tests = {
		filereader: typeof FileReader != 'undefined',
		dnd: 'draggable' in document.createElement('span'),
		formdata: !!window.FormData,
		progress: "upload" in new XMLHttpRequest
	};
	
	this.runtime = {
		itemsInQueue: 0,
		lastQueueIdx: 0
	};
	
	this.acceptedTypes = {
		'image/png': true,
		'image/jpeg': true,
		'image/gif': true
	};
	
	this._objects = {
		holder: null, // file input
		container: null, // we put all elements in this container
		queue: null, // file queue container
		control: null, // queue control container
		handler: null, // triggers file dialog
		upload: null // triggers upload / queue processing
	};
	
	this.queueTimer = null;
	
	this.queue = {};
}

Uploader.prototype.init = function(settings) {	
	this.settings = jQuery.extend(this.defaultSettings, settings);
	
	console.log("init");
	console.log(this.settings);
	
	//file input
	this._objects.holder = $(this.settings.holder);
	
	//uploader container
	this._objects.container = $('<div>',{ 'class': 'media-uploader-container'});
	
	//queue container
	this._objects.queue = $('<div>',{ 'class': 'media-uploader-queue'}).html('- QUEUE -');

	//statistics container
	this._objects.stats = $('<div>',{ 'class': 'media-uploader-stats'}).html('- STATS -');
	
	//control container
	this._objects.control = $('<div>',{ 'class': 'control'});
	this._objects.handler = $('<button>',{ 'class': 'handler'}).html('- SELECT FILES -');
	this._objects.upload = $('<button>',{ 'class': 'upload'}).html('- UPLOAD -');
	
	this._objects.control
		.append(this._objects.handler)
		.append(this._objects.upload);
	
	this._objects.container
		.append(this._objects.control)
		.append(this._objects.queue)
		.append(this._objects.stats);
		
	//append uploader container
	this._objects.holder.after(this._objects.container);
	
	//bind callbacks
	this._objects.handler.bind('click', function(e) {
		e.preventDefault();
		uploader._objects.holder.trigger('click');
	});
	
	this._objects.holder.bind('change', function(e) {
		e.preventDefault();
		uploader.readFiles(this.files);
	});
	
	this._objects.upload.bind('click', function(e) {
		e.preventDefault();
		uploader.processQueue();
	});
	
	this.settings.onInit(this);
};

Uploader.prototype.updateStats = function() {
	
	var stats = 
		'ItemsInQueue: '+this.runtime.itemsInQueue
		+' | lastQueueIdx: '+this.runtime.lastQueueIdx;
	
	this._objects.stats.html(stats);
};

Uploader.prototype.readFiles = function (files) {
	for (var i = 0; i < files.length; i++) {
		//if (this.tests.formdata) formData.append('data[MediaUpload][file]', files[i]);
		// previewfile(files[i]);
		this.addToQueue(files[i]);
	}
	
	if (this.settings.autoProcessQueue) {
		//this.processQueue();
		console.log("autoProcessQueue - DEACTIVATED");
	}
};
		
Uploader.prototype.addToQueue = function (fileObject, retry) {
	console.log("addToQueue");
	console.log(fileObject);
	
	if (typeof retry === undefined)
		retry = 0;
	
	var file = {
		queueId: this.runtime.lastQueueIdx++,
		name: fileObject.name,
		size: fileObject.size,
		type: fileObject.type,
		obj: fileObject,
		_retry: retry,
		_sent: false
	}
	
	if (file._retry > this.settings.maxRetry) {
		console.log("addToQueue maxRetry reached. File:"+file.name);
		return false;
	}
	
	this.queue[file.queueId] = file;
	
	this.settings.onAddToQueue(this, file);
	this.updateStats();
};
		
Uploader.prototype.onAddToQueue = function (uploader, file) {
	
	console.log("onAddToQueue");
	console.log(file);
	
	var div = $('<div>',{ 'class': 'media-uploader-file', 'id': 'media-uploader-queue-'+file.queueId })
	.data('upload', { queueId: file.queueId, name: file.name, size: file.size, type: file.type })
	.append($('<span>',{ 'class': 'media-uploader-file-queueId'}).html(file.queueId))
	.append($('<span>',{ 'class': 'media-uploader-file-name'}).html(file.name))
	.append($('<span>',{ 'class': 'media-uploader-file-size'}).html(file.size))
	.append($('<span>',{ 'class': 'media-uploader-file-type'}).html(file.type))
	.append($('<progress>',{ 'class': 'media-uploader-file-progress', 'max':'100', 'value':0 }).html('Progress not supported'))
	.appendTo(uploader._objects.queue);
	
};

Uploader.prototype.processQueue = function() {
	
	console.log("processQueue");
	//To do this in any ES5-compatible environment, such as Node, Chrome, IE 9+, FF 4+, or Safari 5+:
	console.log(Object.keys(this.queue).length + " items in queue");

	if (this.queue.length < 1) {
		console.log("queue is empty");
		return true;
	}

	//process next item in queue
	for (var prop in this.queue) {
		this.sendFile(this.queue[prop]);
		console.log("processed");
	    break;
	}

};

Uploader.prototype.sendFile = function (file) {
	
	if (this.tests.formdata) { 
		var formData = new FormData();
		formData.append(this._objects.holder.attr('name'), file.obj);
		
		var uploader = this;
		var xhr = new XMLHttpRequest();
		//xhr.addEventListener("progress", this.onTransferProgress, false);
		//xhr.addEventListener("load", this.onTransferComplete, false);
		//xhr.addEventListener("error", this.onTransferFailed, false);
		//xhr.addEventListener("abort", this.onTransferCanceled, false);
		xhr.onload = function (evt) {
			uploader.settings.onTransferComplete(uploader, file, xhr, evt);
		};
		xhr.onprogress = function(evt) {
			uploader.settings.onTransferProgress(uploader, file, xhr, evt);
		};
		xhr.onerror = function(evt) {
			uploader.settings.onTransferFailure(uploader, file, xhr, evt);
		};
		xhr.onabort = function(evt) {
			uploader.settings.onTransferAbort(uploader, file, xhr, evt);
		};
		xhr.open('POST', this.settings.uploadUrl);
		xhr.send(formData);
	} else {
		alert("Failed to send file. FormData not supported.");
		return false;
	}
};

Uploader.prototype.getFileContainer = function (file) {
	return $('#media-uploader-queue-'+file.queueId);
};

Uploader.prototype.onTransferProgress = function (uploader, file, xhr, evt) {
	if (evt.lengthComputable) {
		var complete = (evt.loaded / evt.total * 100 | 0);
		console.log("Progress: "+complete);
		var oProgress = uploader.getFileContainer(file).find('progress').first().val(complete);
	}
};
 

Uploader.prototype.onTransferComplete = function (uploader, file, xhr, evt) {
	console.log(uploader);
	console.log(file)
	console.log(xhr);
	console.log(evt);
	
	if (xhr.status === 200) {
		console.log(xhr.responseText);
		file._sent = true;
	} else {
		console.log('Bad response status: '+xhr.status)
	}
	
	console.log("The transfer is complete.");
	
	uploader.removeFromQueue(file);
};
 
Uploader.prototype.onTransferFailed = function (uploader, file, xhr, evt) {
	alert("An error occurred while transferring the file.");
};
 
Uploader.prototype.onTransferCanceled = function (uploader, file, xhr, evt) {
	alert("The transfer has been canceled by the user.");
};
		
Uploader.prototype.removeFromQueue = function (file) {
	console.log("removeFromQueue");
	console.log(file);
	
	if (file.queueId in this.queue) {
		delete this.queue[file.queueId];
		console.log("removed from queue: "+file.queueId);
	}
	else {
		console.log("failed to removeFromQueue");
	}
	
	uploader.processQueue();
};
