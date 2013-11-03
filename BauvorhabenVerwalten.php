<?php
class BauvorhabenVerwalten
{
	public function getAlleBauvorhaben($app) {
		return function () use ($app) {
			try {
				$user = R::getAll('select * from bauvorhaben'); 
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

	
	public function deleteBauvorhaben($app) {
		return function ($id) use ($app) {
			try {
				$post = R::load('bauvorhaben',$id); //Retrieve
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
	
	public function neuesBauvorhaben($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$neuesBv = json_decode($request->getBody());
				$baunr = R::getCell('select id from bauvorhaben where baunr = ? limit 1', array($neuesBv->baunr));
				if ($baunr == false) {
					$bv = R::dispense('bauvorhaben');
					$bv->baunr = $neuesBv->baunr;
					$bv->name = $neuesBv->name;
					$bv->vorname = $neuesBv->vorname;
					$bv->land = $neuesBv->land;
					$bv->plz = $neuesBv->plz;
					$bv->ort = $neuesBv->ort;
					$bv->str = $neuesBv->str;
					$bv->tel = $neuesBv->tel;
					$bv->mail = $neuesBv->mail;
					$bv->bauleiter = $neuesBv->bauleiter;
					$bv->datlief = $neuesBv->datlief;
					$bv->datuebergabe = $neuesBv->datuebergabe;
					$bv->vgz = $neuesBv->vgz;
					$bv->regiezeit = $neuesBv->regiezeit;
					$bv->kolonne = $neuesBv->kolonne;
					$bv->anzeige = $neuesBv->anzeige;
					$bv->benutzer = $neuesBv->benutzer;
					$bv->monteure = $neuesBv->monteure;
					$id->id = R::store($bv);
					$bv = R::load('bauvorhaben', $id->id);
				} else {
					$id->baunr = $baunr;
				}
				if ($id->id) {
					echo json_encode(R::exportAll($bv), JSON_NUMERIC_CHECK);
				} else {
					echo json_encode($id, JSON_NUMERIC_CHECK);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function putBauvorhaben($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$neuesBv = json_decode($request->getBody());
				$baunr = R::getCell('select id from bauvorhaben where baunr = ? limit 1', array($neuesBv->baunr));
				// Wenn Baunummer noch nicht vorhanden sind neu anlegen
				if ($baunr == false) {
					$bv = R::dispense('bauvorhaben');
					$bv->baunr = $neuesBv->baunr;
					$bv->name = $neuesBv->name;
					$bv->vorname = $neuesBv->vorname;
					$bv->land = $neuesBv->land;
					$bv->plz = $neuesBv->plz;
					$bv->ort = $neuesBv->ort;
					$bv->str = $neuesBv->str;
					$bv->tel = $neuesBv->tel;
					$bv->mail = $neuesBv->mail;
					$bv->bauleiter = $neuesBv->bauleiter;
					$bv->datlief = $neuesBv->datlief;
					$bv->datuebergabe = $neuesBv->datuebergabe;
					$bv->vgz = $neuesBv->vgz;
					$bv->regiezeit = $neuesBv->regiezeit;
					$bv->kolonne = $neuesBv->kolonne;
					$bv->anzeige = $neuesBv->anzeige;
					$bv->benutzer = $neuesBv->benutzer;
					$bv->monteure = $neuesBv->monteure;
					$id->id = R::store($bv);
					$bv = R::load('bauvorhaben', $id->id);
				} else {
					if ($baunr == $neuesBv->id) {
						$bv = R::load('bauvorhaben', $neuesBv->id);
						$bv->baunr = $neuesBv->baunr;
						$bv->name = $neuesBv->name;
						$bv->vorname = $neuesBv->vorname;
						$bv->land = $neuesBv->land;
						$bv->plz = $neuesBv->plz;
						$bv->ort = $neuesBv->ort;
						$bv->str = $neuesBv->str;
						$bv->tel = $neuesBv->tel;
						$bv->mail = $neuesBv->mail;
						$bv->bauleiter = $neuesBv->bauleiter;
						$bv->datlief = $neuesBv->datlief;
						$bv->datuebergabe = $neuesBv->datuebergabe;
						$bv->vgz = $neuesBv->vgz;
						$bv->regiezeit = $neuesBv->regiezeit;
						$bv->kolonne = $neuesBv->kolonne;
						$bv->monteure = $neuesBv->monteure;
						$bv->anzeige = $neuesBv->anzeige;
						$bv->benutzer = $neuesBv->benutzer;
						$id->id = R::store($bv);
						$bv = R::load('bauvorhaben', $id->id);
					} else {
						$id->baunr = $baunr;
					}
				}
				if ($id->id) {
					echo json_encode(R::exportAll($bv), JSON_NUMERIC_CHECK);
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