<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'vendor/Slim-master/Slim/Slim.php';
require 'vendor/RedBean/rb.php';

spl_autoload_register(function($className) {
	switch ($className) {
		case 'Regiestd': include_once('vendor/src/Regiestd.php'); break;
		case 'Montagezeiten': include_once('vendor/src/Montagezeiten.php'); break;
		case 'Kundendienstzeiten': include_once('vendor/src/Kundendienstzeiten.php'); break;	
		case 'Technikzeiten': include_once('vendor/src/Technikzeiten.php'); break;
		case 'BenutzerVerwalten': include_once('vendor/src/BenutzerVerwalten.php'); break;
		case 'BauvorhabenVerwalten': include_once('vendor/src/BauvorhabenVerwalten.php'); break;
		case 'KolonnenVerwalten': include_once('vendor/src/KolonnenVerwalten.php'); break;
		case 'Spesenabrechnung' : include_once('vendor/src/Spesenabrechnung.php'); break;
		case 'ZeitenMontage' : include_once('vendor/src/ZeitenMontage.php'); break; 
		}
}); 

\Slim\Slim::registerAutoloader();

// set up database connection
R::setup('mysql:host=localhost;dbname=hanse','admin','sEKYQ3pdSu3GFsz9');
R::freeze(true);

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Slim\Slim();
$regie = new Regiestd();
$Montagezeiten = new Montagezeiten(); 
$Kundendienstzeiten = new Kundendienstzeiten();
$Technikzeiten = new Technikzeiten();
$BenutzerVerwalten = new BenutzerVerwalten();
$BauvorhabenVerwalten = new BauvorhabenVerwalten();
$KolonnenVerwalten = new KolonnenVerwalten();
$Spesenabrechnung = new Spesenabrechnung();
$ZeitenMontage = new ZeitenMontage();
$app = new \Slim\Slim(array(
    'templates.path' => 'src'
));


$app->add(new \Slim\Middleware\SessionCookie(array('secret' => 'myappsecret')));

$authenticate = function ($app) {
    return function () use ($app) {
        $username = $app->getEncryptedCookie('username');
    	$passwort = $app->getEncryptedCookie('passwort');
		if (validateUserKey($username, $passwort) === false) {
			$app->halt(401);
		}
    };
};

function validateUserKey($username, $passwort) {
	// insert your (hopefully more complex) validation routine here
	$benutzer = R::findOne('benutzer', 'username=?', array($username));
	if ($benutzer && $passwort == $benutzer->passwort) {
		return true;
	} else {
		return false;
	}
}

// Index.html aufrufen
$app->get('/', start($app));
$app->post('/login', login($app));

//Rest ZeitenMontage werwalten
$app->post('/getZeiten'/*,  $authenticate($app)*/, $ZeitenMontage ->getZeiten($app)); 
$app->post('/putZeiten'/*,  $authenticate($app)*/, $ZeitenMontage ->putZeiten($app)); 
$app->post('/getLohnZeiten'/*,  $authenticate($app)*/, $ZeitenMontage ->getLohnZeiten($app));

//Kolonne werwalten
$app->get('/getAlleKolonnen'/*, $authenticate($app)*/, $KolonnenVerwalten ->getAlleKolonnen($app));
$app->post('/neueKolonne'/*,  $authenticate($app)*/, $KolonnenVerwalten ->neueKolonne($app)); 
$app->delete('/deleteKolonne/:id'/*,  $authenticate($app)*/, $KolonnenVerwalten ->deleteKolonne($app));
$app->put('/putKolonne'/*,  $authenticate($app)*/, $KolonnenVerwalten ->putKolonne($app));


//BauvorhabenVerwalten
$app->get('/getAlleBauvorhaben'/*, $authenticate($app)*/, $BauvorhabenVerwalten->getAlleBauvorhaben($app));
$app->post('/neuesBauvorhaben'/*,  $authenticate($app)*/, $BauvorhabenVerwalten->neuesBauvorhaben($app)); 
$app->delete('/deleteBauvorhaben/:id'/*,  $authenticate($app)*/, $BauvorhabenVerwalten->deleteBauvorhaben($app));
$app->put('/putBauvorhaben'/*,  $authenticate($app)*/, $BauvorhabenVerwalten->putBauvorhaben($app));


//Benutzerverwalten
$app->get('/getAlleBenutzer'/*, $authenticate($app)*/, $BenutzerVerwalten->getAlleBenutzer($app));
$app->post('/neuenBenutzer'/*,  $authenticate($app)*/, $BenutzerVerwalten->neuenBenutzer($app)); 
$app->delete('/deleteBenutzer/:id'/*,  $authenticate($app)*/, $BenutzerVerwalten->deleteBenutzer($app));
$app->put('/putBenutzer'/*,  $authenticate($app)*/, $BenutzerVerwalten->putBenutzer($app));


$app->get('/getZeitTechnik/:persnr/:anfDat'/*, $authenticate($app)*/, $Technikzeiten->getZeitTechnik($app));
$app->delete('/zeitDeleteTechnik/:id'/*,  $authenticate($app)*/, $Technikzeiten->zeitDeleteTechnik($app));
$app->get('/getZeitAlleVonBisTechnik/:persnr/:von/:bis'/*,  $authenticate($app)*/, $Technikzeiten->getZeitAlleVonBisTechnik($app)); 
$app->post('/neueZeitTechnik/'/*,  $authenticate($app)*/, $Technikzeiten->neueZeitTechnik($app)); 

$app->get('/getZeitKd/:persnr/:anfDat'/*, $authenticate($app)*/, $Kundendienstzeiten->getZeitKd($app));
$app->delete('/zeitDeleteKd/:id'/*,  $authenticate($app)*/, $Kundendienstzeiten->zeitDeleteKd($app));
$app->get('/getZeitAlleVonBisKd/:persnr/:von/:bis'/*,  $authenticate($app)*/, $Kundendienstzeiten->getZeitAlleVonBisKd($app)); 
$app->post('/neueZeitKd/'/*,  $authenticate($app)*/, $Kundendienstzeiten->neueZeitKd($app));

$app->post('/neueSpesen'/*,  $authenticate($app)*/, $Spesenabrechnung->neueSpesen($app));
$app->post('/spesenVon'/*,  $authenticate($app)*/, $Spesenabrechnung->spesenVon($app));  
$app->get('/getSpesen/:persnr/:anfDat'/*, $authenticate($app)*/, $Spesenabrechnung->getSpesen($app));
$app->delete('/deleteSpesen/:id'/*,  $authenticate($app)*/, $Spesenabrechnung->deleteSpesen($app));
$app->get('/getSpesenVonBis/:persnr/:von/:bis'/*,  $authenticate($app)*/, $Spesenabrechnung->getSpesenVonBis($app)); 
$app->put('/putSpesen'/*,  $authenticate($app)*/, $Spesenabrechnung->putSpesen($app));

$app->get('/getZeit/:baunr/:anfDat'/*, $authenticate($app)*/, $Montagezeiten->getZeit($app));
$app->get('/getZeitAlle/:baunr'/*,  $authenticate($app)*/, $Montagezeiten->getZeitAlle($app));
$app->get('/getZeitAlleVonBis/:baunr/:von/:bis'/*,  $authenticate($app)*/, $Montagezeiten->getZeitAlleVonBis($app)); 
$app->get('/getAlleZeit'/*,$authenticate($app)*/, $Montagezeiten->getAlleZeit($app));
$app->post('/neueZeit'/*, $authenticate($app)*/, $Montagezeiten->neueZeit($app));
$app->delete('/zeitDelete/:id/:baunr'/*,  $authenticate($app)*/, $Montagezeiten->zeitDelete($app));
$app->get('/getZeitGesamt/:baunr'/*,  $authenticate($app)*/, $Montagezeiten->getZeitGesamt($app));
$app->post('/getZeitenLohn'/*,  $authenticate($app)*/, $Montagezeiten->getZeitenLohn($app));
$app->get('/getBvBenutzer/:user'/*,  $authenticate($app)*/, $Montagezeiten->getBvBenutzer($app));
//Restarbeiten
$app->get('/getZeitRest/:persnr/:anfDat'/*, $authenticate($app)*/, $Montagezeiten->getZeitRest($app));
$app->get('/getZeitAlleVonBisRest/:persnr/:von/:bis'/*,  $authenticate($app)*/, $Montagezeiten->getZeitAlleVonBisRest($app)); 

$app->get('/getAlleRegieZeiten/:baunr'/*,  $authenticate($app)*/, $regie->getAlleRegieZeiten($app));
$app->get('/getBvSummeRegieZeiten'/*,  $authenticate($app)*/, $regie->getBvSummeRegieZeiten($app));
$app->post('/neueRegieZeit'/*,  $authenticate($app)*/, $regie->neueRegieZeit($app));
$app->delete('/zeitRegieDelete/:id'/*, $authenticate($app)*/,$regie->zeitRegieDelete($app));
$app->put('/zeitRegiePut'/*,  $authenticate($app)*/, $regie->zeitRegiePut($app));

$app->get('/getBenutzer/:abteilung'/*,  $authenticate($app)*/, getBenutzer($app));
$app->get('/getLohnart/:abteilung'/*,  $authenticate($app)*/, getLohnart($app));
$app->get('/getRegiegrund'/*,  $authenticate($app)*/, getRegiegrund($app));
$app->get('/getAbteilungen'/*,  $authenticate($app)*/, getAbteilungen($app));


$app->post('/regieZeitPost/:regiezeit/:baunr'/*,  $authenticate($app)*/, regieZeitPost($app));


function regieZeitPost($app) {
	return function ($regiezeit, $baunr) use ($app) {
		try {
			R::exec('UPDATE bauvorhaben SET regiezeit = ? WHERE baunr =?', array($regiezeit, $baunr));
			$bauvorhaben =  R::getRow('select
			bauvorhaben.name as bauherr,
			bauvorhaben.ort as bauort,
			bauvorhaben.str as bauortstr,
			bauvorhaben.baunr as id,
			bauvorhaben.kolonne as kolonne,
			bauvorhaben.land as land,
			bauvorhaben.plz as plz,
			bauvorhaben.regiezeit as regiezeit,
			bauvorhaben.vgz as vgz
			from bauvorhaben WHERE baunr=?', array($baunr));
 			if ($bauvorhaben) {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode($bauvorhaben,JSON_NUMERIC_CHECK);
			} else {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode([]);
			}
		} catch (Exception $e) {
			$app->response()->status(400);
			$app->response()->header('X-Status-Reason', $e->getMessage());
		}
	};

} 


function getLohnart($app) {
	return function ($abteilung) use ($app) {
		try {
			$gesamt = 0;
			$r = null;
			$user = R::getAll('select * from lohnarten'); 
			if ($user) {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode($user,JSON_NUMERIC_CHECK);
			} else {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode([]);
			}
		} catch (Exception $e) {
			$app->response()->status(400);
			$app->response()->header('X-Status-Reason', $e->getMessage());
		}
	};
}

function getRegiegrund($app) {
	return function () use ($app) {
		try {
			$gesamt = 0;
			$r = null;
			$user = R::getAll('select * from regiegrund'); 
			if ($user) {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode($user,JSON_NUMERIC_CHECK);
			} else {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode([]);
			}
		} catch (Exception $e) {
			$app->response()->status(400);
			$app->response()->header('X-Status-Reason', $e->getMessage());
		}
	};
}

function getAbteilungen($app) {
	return function () use ($app) {
		try {
			$gesamt = 0;
			$r = null;
			$user = R::getAll('select * from abteilungen'); 
			if ($user) {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode($user,JSON_NUMERIC_CHECK);
			} else {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode([]);
			}
		} catch (Exception $e) {
			$app->response()->status(400);
			$app->response()->header('X-Status-Reason', $e->getMessage());
		}
	};
}  

function getBenutzer($app) {
	return function ($abteilung) use ($app) {
		try {
			$gesamt = 0;
			$r = null;
			if ($abteilung == "Alle"){
				$user = R::getAll('select persnr, name, username from benutzer'); 
			} else {
				$user = R::getAll('select persnr, name, username from benutzer where abteilung=?', array($abteilung)); 
			}
			if ($user) {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode($user,JSON_NUMERIC_CHECK);
			} else {
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode(array());
			}
		} catch (Exception $e) {
			$app->response()->status(400);
			$app->response()->header('X-Status-Reason', $e->getMessage());
		}
	};
} 

function start($app) {
	return function () use ($app) {
		$app->render('index.html');
	};
}

function login($app) {
	return function () use ($app) {
		try {
			$request = $app->request();
			$body = $request->getBody();
			$user = json_decode($request->getBody());
			$r =(object) null;
		//	$r['persnr'] = "test"; 
		//	$benutzer = R::findOne('benutzer', 'username=?', array("eisner"));
			$benutzer = R::getRow('select * from benutzer where username=?', array($user->username)); 
			if ($benutzer && $user->passwort == $benutzer['passwort']) {
				$r->persnr = $benutzer['persnr'];
				$r->name = $benutzer['name'];
				$r->vorname = $benutzer['vorname'];
				$r->benutzer = $benutzer['username'];
				$r->menue = $benutzer['menue'];
				$r->abteilung = $benutzer['abteilung'];
				$r->pruefer = $benutzer['pruefer'];
				$arrbv  = array();
				if ($benutzer['pruefer'] == 1) {
					$rows = R::getAll('SELECT DISTINCT baunr FROM regiestunden');
					foreach ($rows as $row) { 
						$bv = (object) null;
						$bauvorhaben = R::findOne('bauvorhaben', 'baunr=?', array($row['baunr']));
						$bv->baunr = $row['baunr'];
						$bv->bauherr = $bauvorhaben['name'];
						$bv->land = is_null($bauvorhaben['land']) ? '' : $bauvorhaben['land'];
						$bv->plz = is_null($bauvorhaben['plz']) ? '' : $bauvorhaben['plz'];
						$bv->bauort = is_null($bauvorhaben['ort']) ? '' : $bauvorhaben['ort'];
						$bv->bauortstr = is_null($bauvorhaben['str']) ? '' : $bauvorhaben['str'];
						$bv->kolonne = is_null($bauvorhaben['kolonne']) ? '' : $bauvorhaben['kolonne'];
						$bv->vgz = is_null($bauvorhaben['vgz']) ? 0 : $bauvorhaben['vgz'];
						$summe = R::getRow('SELECT SUM(bezahlt) as regiezeit
							FROM regiestunden
							WHERE baunr=? GROUP BY baunr', array($row['baunr']));
						$bv->regiezeit = is_null($summe['regiezeit']) ? 0 : $summe['regiezeit']; 
						array_push($arrbv, $bv);
					}
				}
				$r->bv = $arrbv;
				$response = $app->response();
				$response->header('Content-Type', 'application/json');
				$response->body(json_encode($r, JSON_NUMERIC_CHECK));
			} else {
				echo json_encode([]);
			}
			//if ($benutzer && $user->passwort == $benutzer->passwort) {
				/*
				$r->persnr = $benutzer->persnr;
				$r->name = $benutzer->name;
				$r->vorname = $benutzer->vorname;
				$r->benutzer = $benutzer->username;
				$r->menue = $benutzer->menue;
				$r->abteilung = $benutzer->abteilung;
				$r->pruefer = $benutzer->pruefer;
				//Wenn der Benutzer ein Prüfer ist dann alle Bauvorhaben sonst nur 
				//die für die er eingetragen ist.
				if ($benutzer->pruefer == 1) {
					$rows = R::getAll('SELECT DISTINCT baunr FROM regiestunden');
					$arrbv  = array();
					foreach ($rows as $row) { 
						$bv = null;
						$bauvorhaben = R::findOne('bauvorhaben', 'baunr=?', array($row['baunr']));
						if ($bauvorhaben) {	
							$bv->baunr = $row['baunr'];
							$bv->bauherr = $bauvorhaben['name'];
							$bv->land = is_null($bauvorhaben['land']) ? '' : $bauvorhaben['land'];
							$bv->plz = is_null($bauvorhaben['plz']) ? '' : $bauvorhaben['plz'];
							$bv->bauort = is_null($bauvorhaben['ort']) ? '' : $bauvorhaben['ort'];
							$bv->bauortstr = is_null($bauvorhaben['str']) ? '' : $bauvorhaben['str'];
							$bv->kolonne = is_null($bauvorhaben['kolonne']) ? '' : $bauvorhaben['kolonne'];
							$bv->vgz = is_null($bauvorhaben['vgz']) ? 0 : $bauvorhaben['vgz'];
							$summe = R::getRow('SELECT SUM(bezahlt) as regiezeit
								FROM regiestunden
								WHERE baunr=? GROUP BY baunr', array($row['baunr']));
							$bv->regiezeit = is_null($summe['regiezeit']) ? 0 : $summe['regiezeit']; 
							array_push($arrbv, $bv);
						}
					}
				} 
				*/
				
			   // $r->bv = $arrbv;
				//$_SESSION['user'] = $benutzer->username;
			//	$app->setEncryptedCookie('username', $benutzer->username, '60 minutes');
			//	$app->setEncryptedCookie('passwort', $benutzer->passwort, '60 minutes');
			//	$response = $app->response();
			//	$response->header('Content-Type', 'application/json');
			//	$response->body(json_encode($r, JSON_NUMERIC_CHECK));
			//} else {
			//  echo json_encode([]);
			//}
		} catch (Exception $e) {
			$app->response()->status(400);
			$app->response()->header('X-Status-Reason', $e->getMessage());
		}
	};
}

$app->get('/benutzer/:id', function ($id) use ($app) {
	// query database for single user
	try {
		$benutzer = R::findOne('benutzer', 'username=?', array($id));
		if ($benutzer) {
			// if found, return JSON response
			$app->response()->header('Content-Type', 'application/json');
			// return JSON-encoded response body with query results
			echo json_encode(R::exportAll($benutzer),JSON_NUMERIC_CHECK);	
		} else {
		  // else throw exception
		  echo json_encode([]);
		}
	} catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
	}
});


/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
