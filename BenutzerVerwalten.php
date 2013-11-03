<?php
class BenutzerVerwalten
{
	public function getAlleBenutzer($app) {
		return function () use ($app) {
			try {
				$user = R::getAll('select id, persnr, username, name, vorname, pruefer, menue, abteilung from benutzer'); 
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


	
	
	public function deleteBenutzer($app) {
		return function ($id) use ($app) {
			try {
				$post = R::load('benutzer',$id); //Retrieve
				R::trash($post);             //Delete
				$id = $id;
				$app->response()->header('Content-Type', 'application/json');
				// return JSON-encoded response body with query results
				echo json_encode($id, JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function neuenBenutzer($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$neuerBenutzer = json_decode($request->getBody());
				$username = R::getCell('select id from benutzer where username = ? limit 1',   array($neuerBenutzer->username));
				$persnr = R::getCell('select id from benutzer where persnr = ? limit 1',   array($neuerBenutzer->persnr));
				if (($username == false) && ($persnr == false )) {
					$benutzer = R::dispense('benutzer');
					$benutzer->persnr = $neuerBenutzer->persnr;
					$benutzer->username = $neuerBenutzer->username;
					$benutzer->name = $neuerBenutzer->name;
					$benutzer->vorname = $neuerBenutzer->vorname;
					$benutzer->pruefer = $neuerBenutzer->pruefer;
					$benutzer->menue = $neuerBenutzer->menue;
					$benutzer->abteilung = $neuerBenutzer->abteilung;
					$benutzer->passwort = $neuerBenutzer->passwort;
					$id->id = R::store($benutzer);
					$benutzer = R::load('benutzer', $id->id);
				} else {
					$id->username = $username;
					$id->persnr = $persnr;
				}
				if ($id->id) {
					echo json_encode(R::exportAll($benutzer), JSON_NUMERIC_CHECK);
				} else {
					echo json_encode($id, JSON_NUMERIC_CHECK);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function putBenutzer($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$putBenutzer = json_decode($request->getBody());
				$username = R::getCell('select id from benutzer where username = ? limit 1',   array($putBenutzer->username));
				$persnr = R::getCell('select id from benutzer where persnr = ? limit 1',   array($putBenutzer->persnr));
				// Wenn Benutzername und Persnr. noch nicht vorhanden sind neu anlegen
				if (($username == false) && ($persnr == false )) {
					$benutzer = R::load('benutzer');
					$benutzer->persnr = $putBenutzer->persnr;
					$benutzer->username = $putBenutzer->username;
					$benutzer->name = $putBenutzer->name;
					$benutzer->vorname = $putBenutzer->vorname;
					$benutzer->pruefer = $putBenutzer->pruefer;
					$benutzer->menue = $putBenutzer->menue;
					$benutzer->abteilung = $putBenutzer->abteilung;
					$benutzer->passwort = $putBenutzer->passwort;
					$id->id = R::store($benutzer);
				} else {
					if ($username == false and $persnr == $putBenutzer->id) {
						$benutzer = R::load('benutzer', $putBenutzer->id);
						$benutzer->persnr = $putBenutzer->persnr;
						$benutzer->username = $putBenutzer->username;
						$benutzer->name = $putBenutzer->name;
						$benutzer->vorname = $putBenutzer->vorname;
						$benutzer->pruefer = $putBenutzer->pruefer;
						$benutzer->menue = $putBenutzer->menue;
						$benutzer->abteilung = $putBenutzer->abteilung;
						$benutzer->passwort = $putBenutzer->passwort;
						$id->id = R::store($benutzer);
					}
					if ($username ==$putBenutzer->id and $persnr == false) {
						$benutzer = R::load('benutzer', $putBenutzer->id);
						$benutzer->persnr = $putBenutzer->persnr;
						$benutzer->username = $putBenutzer->username;
						$benutzer->name = $putBenutzer->name;
						$benutzer->vorname = $putBenutzer->vorname;
						$benutzer->pruefer = $putBenutzer->pruefer;
						$benutzer->menue = $putBenutzer->menue;
						$benutzer->abteilung = $putBenutzer->abteilung;
						$benutzer->passwort = $putBenutzer->passwort;
						$id->id = R::store($benutzer);
					}
					if ($username ==$putBenutzer->id and $persnr == $putBenutzer->id) {
						$benutzer = R::load('benutzer', $putBenutzer->id);
						$benutzer->persnr = $putBenutzer->persnr;
						$benutzer->username = $putBenutzer->username;
						$benutzer->name = $putBenutzer->name;
						$benutzer->vorname = $putBenutzer->vorname;
						$benutzer->pruefer = $putBenutzer->pruefer;
						$benutzer->menue = $putBenutzer->menue;
						$benutzer->abteilung = $putBenutzer->abteilung;
						$benutzer->passwort = $putBenutzer->passwort;
						$id->id = R::store($benutzer);
					}
					if (($username) && ($persnr)) {
						$id->username = $username;
						$id->persnr = $persnr;
					}
				}
				if ($id->id) {
					echo json_encode(R::exportAll($benutzer), JSON_NUMERIC_CHECK);
				} else {
					echo json_encode($id, JSON_NUMERIC_CHECK);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};

	} 
	
	
}

?> 