function UploaderUi(settings) {
	
	this.init(settings);

	this.uploader;
	
	this.settings = {
		'prefix': 'upload-',
		'valueHolder': null
	};
	
	this._objects = {
		holder: null, // file input
		container: null, // we put all elements in this container
		valueHolder: null
		//queue: null, // file queue container
		//control: null, // queue control container
		//handler: null, // triggers file dialog
		//upload: null // triggers upload / queue processing
	};
	
}

UploaderUi.prototype.init = function(settings) {
	
	console.log("UI: settings");
	
	this.settings = jQuery.extend(this.settings,settings);
	console.log(this.settings);
	
	if (this.uploader === undefined) {
		this.uploader = new Uploader()
		var eventData = { '_this': this };
		
		jQuery(this.uploader)
			.on('uploader.init', eventData, this.onUploaderInit)
			.on('uploader.filesLoaded', eventData, this.onUploaderFilesLoaded)
			.on('uploader.addToQueue', eventData, this.onUploaderAddToQueue)
			.on('uploader.removeFromQueue', eventData, this.onUploaderRemoveFromQueue)
			.on('uploader.fileTransferProgress', eventData, this.onUploaderFileProgress)
			.on('uploader.fileTransferSuccess', eventData, this.onUploaderFileSuccess)
			.on('uploader.fileTransferFailure', eventData, this.onUploaderFileFailure)
			;
		this.uploader.init(this.settings);
	}
};

/**
 * UploaderUi bindTo(selector)
 * 
 * Converts a file input form field into UploaderUi upload form field
 * Only <input type="file">-type form fields are supported yet!!
 * 
 * @param {string|object} selector		Jquery compatible selector 
 */
UploaderUi.prototype.bindTo =  function (selector) {
	console.log("Bind '"+selector+"'");
	
	var _prefix = this.settings.prefix;
	var _this = this;
	var eventData = { '_this': _this };
	
	// holder
	var holder = $(selector);
	
	// uploader container
	var container = $('<div>',{ 'class': _prefix+'container'});
	
	//queue container
	var queue = $('<div>',{ 'class': _prefix+'queue'});

	// statistics container
	var stats = $('<div>',{ 'class': _prefix+'stats'});
	
	//control container
	var control = $('<div>',{ 'class':  _prefix+'control'});
	var select = $('<a>',{ 'class':  _prefix+'control-select', 'href':'#'})
		.html('- SELECT FILES -')
		.bind('click', function (e) {
			holder.trigger('click');
			e.preventDefault();
			return false;
		});
	var upload = $('<a>',{ 'class': _prefix+'control-upload', 'href':'#'})
		.html('- UPLOAD -')
		.bind('click', function(e) {
			_this.uploader.startUpload();
			e.preventDefault();
			return false;
		});
	
	control
		.append(select)
		.append(upload);
	
	//container
	container
		.append(control)
		.append(queue)
		.append(stats)
		.on('dragover', eventData, function (e) {
			$(this).addClass('dragover');
			return false;
		})
		.on('dragleave', eventData, function (e) {
			$(this).removeClass('dragover');
			return false;
		})
		.on('dragend', eventData, function (e) {
			//TODO this is not triggered?? 
			$(this).removeClass('dragover');
			return false;
		})
		.on('drop', eventData, function (e) {
			console.log("UI: Drop files");
			e.preventDefault();
			e.data._this.uploader.readFiles(e.originalEvent.dataTransfer.files);
			$(this).removeClass('dragover');
		})
	;
		
	//holder
	holder
		.addClass(_prefix+'holder')
		.on('change', eventData, this.selectFiles)
		.after(container);
	
	//container
	container.prepend(holder);
	
	//valueHolder
	var valueHolder = null;
	if (this.settings.valueHolder !== undefined) {
		valueHolder = $(this.settings.valueHolder);
	}
		
	//store jQuery objects
	this._objects.holder = holder;
	this._objects.container = container;
	this._objects.valueHolder = valueHolder;
	
	//configure uploader
	this.uploader.settings.postField = holder.attr('name');
};


/**
 * Triggered by Holder
 */
UploaderUi.prototype.selectFiles = function(event) {
	
	console.log("UI: selectFiles(event)")
	console.log(event);
	
	event.data._this.uploader.readFiles(event.target.files);
};


/**
 * Triggered by Uploader
 */
UploaderUi.prototype.onUploaderFilesLoaded = function(event) {
	
	console.log("UI: onFilesLoaded(event)")
	console.log(event);
	
	event.target.startUpload();
	//event.data._this.uploader.startUpload();
};


/**
 * Triggered by Uploader
 */
UploaderUi.prototype.onUploaderInit = function(event) {
	//- modify uploader after init here -
	
	console.log("UI: onUploaderInit(event)")
	//TODO trigger user-defined callback
};

/**
 * Triggered by Uploader
 */
UploaderUi.prototype.onUploaderAddToQueue = function (event, queueId, file) {
	
	console.log("UI: onUploadAddToQueue(event, queueId, file): " + queueId);
	console.log(event);
	
	var _this = event.data._this;
	var _prefix = _this.settings.prefix;
	
	var div = $('<div>',{ 'class': _prefix+'file', 'id': _prefix+'queue-'+queueId })
	.data('upload', { queueId: queueId, name: file.name, size: file.size, type: file.type })
	.append($('<div>',{ 'class': _prefix+'file-queueId'}).html(queueId))
	.append($('<div>',{ 'class': _prefix+'file-name'}).html(file.name))
	.append($('<div>',{ 'class': _prefix+'file-size'}).html(file.size))
	.append($('<div>',{ 'class': _prefix+'file-type'}).html(file.type))
	.append($('<progress>',{ 'class': _prefix+'file-progress', 'max':'100', 'value':0 }).html('Progress not supported'))
	.append($('<div>',{ 'class': _prefix+'file-status'}).html('- status -'))
	.append($('<div>',{ 'class': _prefix+'file-control'})
			.append($('<button>',{ 'class': _prefix+'file-abort'}).html('[abort]'))
			.append($('<button>',{ 'class': _prefix+'file-remove'}).html('[remove]'))
	)
	.appendTo(_this._objects.container.find('.'+_prefix+'queue')[0]);

	$('button.upload-file-abort, button.upload-file-remove').click(function(e) {
		console.log("Queue file button click");
		
		event.target.removeFromQueue(queueId);
		//_this.uploader.removeFromQueue(queueId)
		event.preventDefault();
	});

	_this.updateStats();
};


/**
 * Triggered by Uploader
 */
UploaderUi.prototype.onUploaderRemoveFromQueue = function (event, queueId) {
	
	console.log("UI: onUploadRemoveFromQueue(event, queueId, file): "+queueId);
	console.log(event);

	var _this = event.data._this;
	var fileContainer = $('#'+_this.settings.prefix+'queue-'+queueId);
	fileContainer.addClass('removed');

	_this.updateStats();
};

/**
 * Triggered by Uploader
 */
UploaderUi.prototype.onUploaderFileProgress = function (event, queueId, progressEvent) {

	console.log("UI: onUploaderFileProgress(event, queueId, completed)"+queueId);

	var completed = (progressEvent.loaded / progressEvent.total * 100 | 0);
	
	event.data._this.updateFileProgress(queueId, completed);
	event.data._this.updateStats();
};

/**
 * Triggered by Uploader
 */
UploaderUi.prototype.onUploaderFileSuccess = function (event, queueId, responseText) {
	
	console.log("UI: onUploaderFileSuccess(event, queueId, responseText) QueueId:"+queueId);

	var response = JSON.parse(responseText);
	console.log(response);
	
	console.log(event.data._this._objects.valueHolder);
	for(var field in response.files) {
		console.log(field);
		for(var idx in response.files[field]) {
			console.log(idx);
			var file = response.files[field][idx];
			console.log(file);
			console.log(response.files[field][idx]['name']);
			event.data._this._objects.valueHolder.val(response.files[field][idx]['name']);
		}
	}
	
	event.data._this.updateFileProgress(queueId, 100);
	event.data._this.updateFileStatus(queueId, response);
	event.data._this.updateStats();
};

/**
 * Triggered by Uploader
 */
UploaderUi.prototype.onUploaderFileFailure = function (event, queueId) {
	
	console.log("UI: onUploaderFileFailure(event, queueId)");

	event.data._this.updateFileStatus(queueId, "Upload error");
	event.data._this.updateStats();
};

/**
 * Update file upload progress in DOM
 */
UploaderUi.prototype.updateFileProgress = function (queueId, completed) {

	var fileContainer = $('#'+this.settings.prefix+'queue-'+queueId);
	var fileProgress = fileContainer.find('.'+this.settings.prefix+'file-progress');
	
	if (typeof fileProgress === 'object') {
		fileProgress.attr('value', completed);
	} else {
		console.log("UI: File progress container not found: "+queueId);
	}
};

/**
 * Update file upload status in DOM
 */
UploaderUi.prototype.updateFileStatus = function (queueId, response) {

	var fileContainer = $('#'+this.settings.prefix+'queue-'+queueId);
	var fileStatus = fileContainer.find('.'+this.settings.prefix+'file-status');
	
	if (typeof fileStatus === 'object') {
		//fileStatus.html(statusStr);
		fileStatus.html(response.message);
	} else {
		console.log("UI: File status container not found "+queueId);
	}
};

UploaderUi.prototype.updateStats = function() {
	
	console.log("UI: updateStats()");
	
	var runtime = this.uploader.runtime;
	var statsContainer = this._objects.container.find('.'+this.settings.prefix+'stats');
	
	var stats = $('<dl>');
	
	for (var prop in runtime) {
		stats.append($('<dt>').html(prop));
		stats.append($('<dd>').html(runtime[prop]));
	}
	
	statsContainer.html(stats);
};

/*

Uploader.prototype.updateQueueItem = function (file) {
	console.log("updateQueueItem");
	
	var fileContainer = this.getFileContainer(file);
	var status = "Unknown";
	
	if (file._sent) {
		status = 'OK';
		fileContainer.find('.upload-file-abort').hide();
		delete this.uploads[file.queueId];
	} else if (file._error !== false) {
		status = 'Error: '+file._error;
	} else {
		status = 'Not sent yet';
	}
	
	fileContainer.find('.upload-file-status').html(status);
};

Uploader.prototype.updateStats = function() {
	
	var stats = 
		'ItemsInQueue: '+this.runtime.itemsInQueue
		+' | lastQueueIdx: '+this.runtime.lastQueueIdx;
	
	this._objects.stats.html(stats);
};


*/

