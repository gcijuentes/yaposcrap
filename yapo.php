<?php

require_once './vendor/autoload.php';

use GuzzleHttp\Client;
use League\Csv\Writer;

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['url_yapo'])){
        $data = array();
        for($i=0; $i<=10; $i++){
            $i += 1;
            $url_request = $_POST['url_yapo'].'&o='.$i;
            var_dump($url_request);
            $client = new Client();
            $user_agent = array(
                'desktop' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36',
                'mobile' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1'
            );
            $r = $client->request('GET', $url_request, array(
                'headers' => array(
                    'User-Agent' => $user_agent['desktop']
                )
            ));

            $html = (string) $r->getBody();
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML($html);
            $xpath = new DOMXPath($doc);
            $anuncios = $xpath->evaluate('//table[@id="hl"]//tr');
			
			$fp = fopen('yapoSql.sql', 'a+');
			

            foreach($anuncios as $anuncio){
                $titulo = $xpath->evaluate('.//a[@class="title"]', $anuncio);
                if(isset($titulo[0]->textContent)){
                    $titulo = trim($titulo[0]->textContent);
					
                }else{
                    $titulo = null;
                }
                $precio = $xpath->evaluate('.//span[@class="price"]', $anuncio);
                if(isset($precio[0]->textContent)){
                    $precio = trim($precio[0]->textContent);
                }else{
                    $precio = null;
                }

                $anio = $xpath->evaluate('.//div[@class="icons"]//i[@class="fal fa-calendar-alt icons__element-icon"]/following-sibling::span', $anuncio);
                if(isset($anio[0]->textContent)){
                    $anio = trim($anio[0]->textContent);
                }else{
                    $anio = null;
                }

                $km = $xpath->evaluate('.//div[@class="icons"]//i[@class="fal fa-tachometer icons__element-icon"]/following-sibling::span', $anuncio);
                if(isset($km[0]->textContent)){
                    $km = trim($km[0]->textContent);
                }else{
                    $km = null;
                }

                $transmision = $xpath->evaluate('.//div[@class="icons"]//i[@class="fal fa-cogs icons__element-icon"]/following-sibling::span', $anuncio);
                if(isset($transmision[0]->textContent)){
                    $transmision = trim($transmision[0]->textContent);            
                }else{
                    $transmision = null;
                }

                $img = $xpath->evaluate('.//td[@class="listing_thumbs_image"]//div[@class="link_image"]//img', $anuncio);
                if(isset($img) && isset($img[0])){
                    $img = $img[0]->getattribute('src');
                }else{
                    $img = null;
                }

                $region = $xpath->evaluate('.//td[@class="clean_links"]//span[@class="region"]', $anuncio);
                if(isset($region[0])){
                    $region = trim($region[0]->textContent);
                }else{
                    $region = null;
                }

                $comuna = $xpath->evaluate('.//td[@class="clean_links"]//span[@class="commune"]', $anuncio);
                if(isset($comuna[0])){
                    $comuna = trim($comuna[0]->textContent);
                }else{
                    $comuna = null;
                }
				
				$urlAviso = $xpath->evaluate('.//a[@class="title"]', $anuncio);
                if(isset($urlAviso[0]->textContent)){
                    $urlAviso = trim($urlAviso[0]->getattribute('href'));
					
                }else{
                    $urlAviso = null;
                }
				
				$img2 = str_replace("thumbsli","images",$img);
				
                array_push($data, array('titulo' => $titulo,
                                        'imagen' => $img2,
                                        'precio' => $precio,
                                        'anio' => $anio,
                                        'km' => $km,
                                        'transmision' => $transmision,
                                        'region' => $region,
                                        'comuna' => $comuna,
                                        'url' => $urlAviso));
										
				$text = "INSERT INTO `vehiculo` (`id`, `kilometraje`, `combustible`, `transmision`, `consumo`, `valor`, `comentario`, `color`, `litros_motor`, `cilindros`, `ciudad`, `telefono`, `mail`, `tipo`, `id_tipo_vehiculo`, `id_aviso`, `patente`, `id_ciudad`, `url`) 
				VALUES (NULL, '1', '1', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', '1', '1', '1', '2', '1');";
									
				$sqlYapo = "INSERT INTO `yapo` (`id`, `titulo`, `imagen`, `precio`, `anio`, `km`, `transmision`, `region`, `comuna`, `url`) 
				VALUES ('', '$titulo', '$img2', '$precio', '$anio', '$km', '$transmision', '$region', '$comuna', '$urlAviso');";
				
				fwrite($fp, $sqlYapo);
            }
        }
        $filecsv = fopen('yapo.csv', 'w');
        fclose($filecsv);
        $headers_csv = array('titulo', 'imagen', 'precio', 'anio', 'km', 'transmision', 'region', 'comuna','url');
        $csv = Writer::createFromStream(fopen('yapo.csv', 'r+'));
		
		$fh = fopen('yapo.sql', "r+");
		

        $csv->insertOne($headers_csv);
        foreach($data as $anuncio){
			
			$text = "INSERT INTO `vehiculo` (`id`, `kilometraje`, `combustible`, `transmision`, `consumo`, `valor`, `comentario`, `color`, `litros_motor`, `cilindros`, `ciudad`, `telefono`, `mail`, `tipo`, `id_tipo_vehiculo`, `id_aviso`, `patente`, `id_ciudad`, `url`) VALUES (NULL, '1', '1', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', '1', '1', '1', '2', '1');";
			
			fwrite($fh, $text);
			
            $csv->insertOne(array(
                $anuncio['titulo'],
                $anuncio['imagen'],
                $anuncio['precio'],
                $anuncio['anio'],
                $anuncio['km'],
                $anuncio['transmision'],
                $anuncio['region'],
                $anuncio['comuna'],
				$anuncio['url']
            ));
        }
        #$csv->output('yapo.csv');
        header('Location: index.php');
        
        
    }else{
        http_response_code(400);
    }
}else{
    http_response_code(405);
}
