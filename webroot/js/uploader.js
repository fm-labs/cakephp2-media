var uploader = {
		
		defaultSettings: {
			upload: '#upload', // trigger upload / proccess queue
			handler: '#handler', // select files
			holder: '#file', // file input
			progress: '#progress', // progress bar
			queue: '#queue', // queue container
			uploadUrl: 'upload.php',
			maxRetry: 3,
			autoProcessQueue: true,
			onInit: function (uploader) {}
		},
		
		runtime: {
			itemsInQueue: 0,
			lastQueueIdx: 0
		},
		
		tests: {
			filereader: typeof FileReader != 'undefined',
			dnd: 'draggable' in document.createElement('span'),
			formdata: !!window.FormData,
			progress: "upload" in new XMLHttpRequest
		},
		
		acceptedTypes: {
			'image/png': true,
			'image/jpeg': true,
			'image/gif': true
		},
		
		objects: {
			handler: null,
			holder: null,
			progress: null
		},
		
		queueTimer: null,
		
		queue: [],
		
		init: function(settings) {
			this.settings = jQuery.extend(this.defaultSettings, settings);
			
			this.objects.holder = $(this.settings.holder);
			this.objects.handler = $(this.settings.handler);
			this.objects.progress = $(this.settings.progress);
			this.objects.queue = $(this.settings.queue);
			this.objects.upload = $(this.settings.upload);
			
			//bind on file change

			this.objects.handler.bind('click', function(e) {
				e.preventDefault();
				uploader.objects.holder.trigger('click');
			});

			this.objects.holder.bind('change', function(e) {
				e.preventDefault();
				uploader.readFiles(this.files);
			});

			this.objects.upload.bind('click', function(e) {
				e.preventDefault();
				uploader.processQueue();
			});
			
			this.settings.onInit(this);
		},
		
		readFiles: function (files) {
			for (var i = 0; i < files.length; i++) {
				//if (this.tests.formdata) formData.append('data[MediaUpload][file]', files[i]);
				// previewfile(files[i]);
				this.addFileToQueue(files[i]);
			}
			
			if (this.settings.autoProcessQueue) {
				//this.processQueue();
				console.log("autoProcessQueue - DEACTIVATED");
			}
		},
		
		addFileToQueue: function (fileObject, retry) {
			console.log("addFileToQueue");
			console.log(fileObject);
			
			if (typeof retry === undefined)
				retry = 0;
			
			var file = {
				queueId: this.runtime.lastQueueIdx++,
				name: fileObject.name,
				size: fileObject.size,
				type: fileObject.type,
				obj: fileObject,
				_retry: retry
			}
			
			if (file._retry > this.settings.maxRetry) {
				console.log("addFileToQueue maxRetry reached. File:"+file.name);
				return false;
			}
			
			this.queue.push(file);
			
			var div = $('<div>',{ 'class': 'media-uploader-file', 'id': 'media-uploader-queue-'+file.queueId })
				.data('upload', { queueId: file.queueId, name: file.name, size: file.size, type: file.type })
				.append($('<span>',{ 'class': 'media-uploader-file-queueId'}).html(file.queueId))
				.append($('<span>',{ 'class': 'media-uploader-file-name'}).html(file.name))
				.append($('<span>',{ 'class': 'media-uploader-file-size'}).html(file.size))
				.append($('<span>',{ 'class': 'media-uploader-file-type'}).html(file.type))
				.appendTo(this.objects.queue);
			
		},
		
		sendFile: function (file) {
			
			var progress = this.objects.progress;
			
			var formData = null;
			if (this.tests.formdata) { 
				formData = new FormData();
				formData.append('data[MediaUpload][file]', file);
			}
			
			// now post a new XHR request
			if (this.tests.formdata) {
				var xhr = new XMLHttpRequest();
				xhr.open('POST', this.settings.uploadUrl);
				xhr.onload = function() {
					progress.val(100);
				};
			}
				
			if (this.tests.progress) {
				xhr.upload.onprogress = function (event) {
					if (event.lengthComputable) {
						var complete = (event.loaded / event.total * 100 | 0);
						progress.val(complete);
					}
				}
			}
			
			xhr.send(formData);
		},
		
		processQueue: function(stop) {
			
			console.log("processQueue");
			console.log(this.queue.length + " items in queue");

			if (this.queue.length < 1) {
				console.log("queue is empty");
				return true;
			}

			//process next item in queue
			this.processQueueItem(this.queue.shift());
			
			console.log("now " + this.queue.length + " items in queue");
			
			//stop further processing
			if (stop === true)
				return true;
			
			//trigger further processing
			//this.processQueue();
		},
		
		processQueueItem: function (file) {
			console.log("processQueueItem");
			console.log(file);
			
			if (this.sendFile(file.obj)) {
				console.log("sendFile successful");
			} else {
				console.log("sendFile failed");
				//this.addFileToQueue(file.obj, file._retry + 1);
			}
		}
};