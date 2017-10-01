<?php
App::uses('AppHelper', 'View/Helper');
App::uses('MediaThumb', 'Media.Lib');

/**
 * ThumbHelper
 *
 * @property HtmlHelper $Html
 */
class ThumbHelper extends AppHelper {

	public $helpers = array('Html');

/**
 * Returns url of thumbnail
 *
 * @param string $path Path to original file. Uses IMAGES as root path if path is relative
 * @param array $params Array of phpThumb parameters
 */
	public function imageUrl($path, $params = array()) {
		return MediaThumb::createThumbUrl($path, $params);
	}

/**
 * Returns HTML string to display thumbnail
 *
 * @param string $path Path to original file. See LibPhpThumb::getThumbnailUrl()
 * @param array $options Array of Html::image()-options and phpthumb params
 */
	public function image($path, $options = array()) {
		$thumbParams = array();
		if (isset($options['width'])) {
			$thumbParams['w'] = $options['width'];
			unset($options['width']);
		}
		if (isset($options['height'])) {
			$thumbParams['h'] = $options['height'];
			unset($options['height']);
		}
		if (isset($options['quality'])) {
			$thumbParams['q'] = $options['quality'];
			unset($options['quality']);
		}

		if (isset($options['thumb'])) {
			$thumbParams = array_merge($thumbParams, $options['thumb']);
			unset($options['thumb']);
		}

        $thumbUrl = null;

        try {
            $thumbUrl = $this->imageUrl($path, $thumbParams);
            if (!$thumbUrl) {
                return false;
            }
        } catch (Exception $e) {
            debug($e->getMessage());
            return false;
        }

		/*
		if (isset($options['url'])) {
			if($options['url'] == '{source}') {
				$options['url'] = Router::url('/',true).IMAGES_URL.$path;
			}
			elseif($options['url'] == '{thumb}') {
				$options['url'] = Router::url('/',true).IMAGES_URL.$thumbUrl;
			}
		}
		*/

		return $this->Html->image($thumbUrl, $options);
	}
}