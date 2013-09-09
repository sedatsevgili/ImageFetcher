<?php
class ImageFetcher {
	
	private static function _getResponse($url) {
		$curlHandler = curl_init($url);
		curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlHandler, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/28.0.1500.71 Chrome/28.0.1500.71 Safari/537.36");
		curl_setopt($curlHandler, CURLOPT_FOLLOWLOCATION, true);
		$response = curl_exec($curlHandler);
		if(false === $response) {
			curl_close($response);
			echo "i am deeply deeply sorry" . PHP_EOL;
			exit;
		}
		curl_close($curlHandler);
		return $response;
	}
	
	public static function fetchFromGoogle($keyword) {
		if(!is_dir('images')) {
			mkdir('images') or die('Could not create images directory, please check your permissions');
		}
		
		$rawKeyword = $keyword;
		$keyword = urlencode($keyword);
		$url = "http://images.google.com/search?safe=off&site=&tbm=isch&source=hp&biw=1855&bih=574&q=" . $keyword . "&oq=" . $keyword . "&gs_l=img.12..0l10.8032.8496.0.14552.6.5.0.0.0.0.158.557.2j3.5.0....0...1ac.1.26.img..2.4.398.KaBbv_bw_vc";
		$response = self::_getResponse($url);
		$count = preg_match_all("/e\.src='([^']+)'/", $response, $matches);
		if($count === 0) {
			echo "i could not find any image" . PHP_EOL; 
			exit;
		}
		
		$imageNames = array();
		for($i = 0; $i < count($matches[1]); $i++) {
			$match = $matches[1][$i];
			$typeMatchCount = preg_match_all('/data:image\/([^;]+)/', $match, $typeMatches);
			if($typeMatchCount == 0) {
				echo "could not fetch type" . PHP_EOL;
				continue;
			}
			$imageType = $typeMatches[1][0];
			$imageName = $rawKeyword . '.' . $imageType;
			$nameCount = 0;
			while(in_array($imageName, $imageNames)) {
				$nameCount++;
				$imageName = $rawKeyword . '-' . $nameCount . '.' . $imageType;
			}
			$imageNames[] = $imageName;
			
			$contentMatchCount = preg_match_all('/data:image\/[^;]+;base64,(.+)/', $match, $contentMatches);
			if($contentMatchCount == 0) {
				continue;
			}
			$fileContent = base64_decode($contentMatches[1][0]);
			
			file_put_contents('images/' . $imageName, $fileContent);
		}
	}
	
}

