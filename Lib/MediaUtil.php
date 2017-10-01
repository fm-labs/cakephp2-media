<?php
class MediaUtil {
	
	/**
	 * Validate mime type
	 * @param string $mime The mime type to check
	 * @param array|string $allowed List of allowed mime types. Use '*' for all mime types or e.g. 'image/*' for all images
	 * @return boolean
	 */
	static function validateMimeType($mime, $allowed = array()) {
	
		if (is_string($allowed)) {
			if($allowed == "*")
				return true;
			else
				$allowed = array($allowed);
		}
	
		$mime = explode('/',$mime);
	
		foreach($allowed as $type) {
			$type = explode('/', $type);
			if ($mime[0] != $type[0])
				continue;
	
			if ($type[1] == "*" || $mime[1] == $type[1])
				return true;
		}
	
		return false;
	}
	
	/**
	 * Validate file extension
	 * @param string $ext
	 * @param array|string $allowed List of allowed extensions. Use '*' for all extensions
	 * @return boolean
	 */
	static function validateFileExtension($ext, $allowed = array()) {
	
		if (is_string($allowed)) {
			if($allowed == "*")
				return true;
			else
				$allowed = array($allowed);
		}
	
		return in_array($ext, $allowed);
	}
	
	/**
	 * Split basename
	 * @param string $basename File basename (filename with extension)
	 * @return array Returns in format array($filename, $ext, $dotExt)
	 */
	static public function splitBasename($basename) {
	
		if (strrpos($basename,'.') !== false) {
			$parts = explode('.', $basename);
			$ext = array_pop($parts);
			$dotExt = '.'.$ext;
			$filename = join('.',$parts);
		} else {
			$ext = $dotExt = null;
			$filename = $basename;
		}
	
		return array($filename, $ext, $dotExt);
	
	}
	
}