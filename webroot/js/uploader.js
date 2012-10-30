function Uploader() {
	
	this.settings = {
		uploadUrl: 'upload.php',
		postField: 'file',
		//maxRetry: 3,
	};
	
	this.tests = {
		filereader: typeof FileReader != 'undefined',
		dnd: 'draggable' in document.createElement('span'),
		formdata: !!window.FormData,
		progress: "upload" in new XMLHttpRequest
	};
	
	this.runtime = {
		lastQueueId: 0,
		queueItems: 0,
		queueTotal: 0,
		uploadTotal: 0
	};
	
	this.queue = {};
	
	this.timer = {};
}

/**
 * Uploader init(settings)
 * 
 * Extend settings and trigger 'upload.init'
 */
Uploader.prototype.init = function(settings) {	
	this.settings = jQuery.extend(this.settings, settings);
	
	console.log("Uploader: init");
	console.log(this.settings);

	jQuery(this).trigger('uploader.init');
};


/**
 * Uploader readFiles(files)
 * 
 * Loop through FileList and add files to queue
 * No events will be triggered
 * 
 * @param {object} files
 */
Uploader.prototype.readFiles = function (files) {
	console.log("Uploader: readFiles(files)");
	
	for (var i = 0; i < files.length; i++) {
		//if (this.tests.formdata) formData.append('data[MediaUpload][file]', files[i]);
		// previewfile(files[i]);
		this.addToQueue(files[i]);
	}

	jQuery(this).trigger('uploader.filesLoaded');
};

/**
 * Uploader addToQueue(file)
 * 
 * Create unique queueId and push to queue list (object)
 * Triggers 'uploader.addToQueue' afterwards 
 * 
 * @param {object} file
 */
Uploader.prototype.addToQueue = function (file) {
	console.log("addToQueue: "+file.name);
	
	//unique queueId
	var queueId = ++this.runtime.lastQueueId;
	
	//add to queue list
	this.queue[queueId] = file;
	
	//calculate total filesize in queue
	this.runtime.queueTotal += file.size;
	//calculate items in queue
	//requires ES5-compatible environment, such as Node, Chrome, IE 9+, FF 4+, or Safari 5+: [web source]
	//TODO Keep this DRY. Also in removeFromQueue();
	this.runtime.queueItems = Object.keys(this.queue).length;
	console.log(this.runtime.queueItems + " items in queue");

	jQuery(this).trigger('uploader.addToQueue', [queueId, file]);
	
};

/**
 * Uploader removeFromQueue(queueId)
 * 
 * Remove file by given queueId from queue list
 * Triggers 'uploader.removeFromQueue' afterwards
 * 
 * @param {String} queueId
 */
Uploader.prototype.removeFromQueue = function (queueId) {
	console.log("Uploader: removeFromQueue(queuId): "+queueId);
	
	if (queueId in this.queue) {
		var file = this.queue[queueId];
		delete this.queue[queueId];
		console.log("removed from queue: "+queueId);
		
		//update runtime statistics
		this.runtime.queueItems = Object.keys(this.queue).length;
		this.runtime.queueTotal -= file.size;
		this.runtime.uploadTotal += file.size;
		
		console.log(this.runtime.queueItems + " items in queue now");
	}
	else {
		//TODO trigger failure callback
		console.log("failed to removeFromQueue: "+queueId);
		return false;
	}

	jQuery(this).trigger('uploader.removeFromQueue', [queueId]);
};

/**
 * Uploader startUpload()
 * 
 * Take next file in queue and send
 */
Uploader.prototype.startUpload = function() {
	
	console.log("Uploader: startUpload()");
	
	//check if items in queue
	if (this.runtime.queueItems < 1) {
		console.log("Uploader: Queue is empty");
		return true;
	}

	//take first entry
	for (var queueId in this.queue) {
		var file = this.queue[queueId];
		jQuery(this).trigger('uploader.beforeUpload', [queueId, file]);
		var _this = this;
		var transfer = function () { _this.transferFile(queueId,file); };
		//this.timer[queueId] = setTimeout(transfer, 1000);
		transfer();
		break;
	}
};

/**
 * Uploader transferFile(queueId, file)
 * 
 * Send file object via xhr2
 * 
 * @param {string} queueId		Internal QueueId
 * @param {object} file		File object
 */
Uploader.prototype.transferFile = function (queueId, file) {
	
	console.log("Uploader: transferFile(queueId, file)");
	
	if (this.tests.formdata) {
		
		//safe form data post field
		var postField = this.settings.postField;
		if (postField.length < 1) {
			console.log("Invalid uploader.settings.postField: "+postField);
			postField = 'file';
		}
		
		//build form data
		var formData = new FormData();
		formData.append(postField, file);
		
		//eventData recieved by xhr callback methods
		var eventData = { '_this' : this, 'queueId': queueId };
		
		//xhr request
		var xhr = new XMLHttpRequest();
		xhr.open('POST', this.settings.uploadUrl);
		xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Identify as ajax call
		jQuery(xhr).on('load', eventData, this.onTransferComplete );
		jQuery(xhr).on('abort', eventData, this.onTransferCancel );
		jQuery(xhr).on('error', eventData, this.onTransferFailure );
		jQuery(xhr.upload).on('progress', eventData, this.onTransferProgress );
		xhr.send(formData);
		
	} else {
		alert("Failed to send file. FormData not supported.");
		//trigger error handler
		return false;
	}
};


/**
 * Uploader onTransferComplete(e)
 * 
 * XHR callback for xhr.onload
 * @todo Keep this DRY
 */
Uploader.prototype.onTransferComplete = function (e) {
	
	console.log("Uploader: onTransferComplete(e). QueueId: "+e.data.queueId);
	
	var xhr = e.target;
	var uploader = e.data._this;
	
	//check xhr status
	if (xhr.status === 200) {
		console.log(xhr.responseText);
		uploader.onTransferSuccess(e);
	} else {
		//TODO handle failed uploads
		//TODO retry failed upoads
		console.log('Bad response status: '+xhr.status);
		uploader.onTransferFailure(e);
	}
	
	console.log("The transfer is complete.");
	
	//remove from queue
	uploader.removeFromQueue(e.data.queueId);
	uploader.startUpload();
};

/**
 * Uploader onTransferSuccess(e)
 * 
 * XHR callback for xhr.onload will be directed to this method if successful
 */
Uploader.prototype.onTransferSuccess = function(e) {

	console.log("Uploader: onTransferSuccess(e). QueueId: "+e.data.queueId);
	jQuery(e.data._this).trigger('uploader.fileTransferSuccess', [e.data.queueId, e.target.responseText]);	
}

/**
 * Uploader onTransferProgress(e)
 * 
 * XHR callback for xhr.upload.onprogress
 * @todo Keep this DRY
 */
Uploader.prototype.onTransferProgress = function (e) {
	
	console.log("Uploader: onTransferProgress(e). QueueId: "+e.data.queueId);

	if (e.originalEvent.lengthComputable) {
		console.log("Uploader Progress Computable");
		jQuery(e.data._this).trigger('uploader.fileTransferProgress', [e.data.queueId, e.originalEvent]);
	} else {
		console.log("Uploader Progress NOT Computable");
	}
};

/**
 * Uploader onTransferFailure(e)
 * 
 * XHR callback for xhr.onerror
 * @todo Keep this DRY
 */
Uploader.prototype.onTransferFailure = function (e) {
	
	console.log("Uploader: onTransferFailure(e). QueueId: "+e.data.queueId
			+" - An error occurred while transferring the file.");
	
	jQuery(e.data._this).trigger('uploader.fileTransferFailure', [e.data.queueId]);
};

/**
 * Uploader onTransferCancel(e)
 * 
 * XHR callback for xhr.onabort
 * @todo Keep this DRY
 */
Uploader.prototype.onTransferCancel = function (e) {
	
	console.log("Uploader: onTransferCancel(e). QueueId: "+e.data.queueId
			+" - The transfer has been canceled by the user.");

	jQuery(e.data._this).trigger('uploader.fileTransferCancel', [e.data.queueId]);
	
};

