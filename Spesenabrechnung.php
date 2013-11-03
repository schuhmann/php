<?php
class Spesenabrechnung
{
	public function deleteSpesen($app) {
		return function ($id) use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$post = R::load('spesen',$id); //Retrieve
				R::trash($post);             //Delete
				$r->id = $id;
				$app->response()->header('Content-Type', 'application/json');
				// return JSON-encoded response body with query results
				echo json_encode($r, JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function neueSpesen($app) {
		return function () use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$request = $app->request();
				$body = $request->getBody();
				$neueSpesen = json_decode($request->getBody());
				$persnr = array();
				if (!is_array($neueSpesen->persnr)) { 
					array_push($persnr, $neueSpesen->persnr);
				} else {
					$persnr = $neueSpesen->persnr;
				}
				foreach ($persnr as $nr) {
					$spesen = R::findOne('spesen', 'persnr=? AND anfdat=?', array($nr,$neueSpesen->anfdat));
					if ($spesen) {
						$spesen->persnr = $nr;
						$spesen->anfdat = $neueSpesen->anfdat;
						$spesen->land = $neueSpesen->land;
						$spesen->reisebeginn = $neueSpesen->reisebeginn;
						$spesen->reiseende = $neueSpesen->reiseende;
						$spesen->abwesenheitsstd = $neueSpesen->abwesenheitsstd;
						$spesen->tagegeld = $neueSpesen->tagegeld;
						$spesen->tagegeldko = $neueSpesen->tagegeldko;
						$spesen->uebernachtung = $neueSpesen->uebernachtung; 
						$spesen->uebernachtungko = $neueSpesen->uebernachtungko;
						$spesen->benutzer_persnr = $neueSpesen->benutzer_persnr;
						$spesen->timeStamp = R::$f->now();
						$id = R::store($spesen);
					} else {
						$spesen = R::dispense('spesen');
						$spesen->persnr = $nr;
						$spesen->anfdat = $neueSpesen->anfdat;
						$spesen->land = $neueSpesen->land;
						$spesen->reisebeginn = $neueSpesen->reisebeginn;
						$spesen->reiseende = $neueSpesen->reiseende;
						$spesen->abwesenheitsstd = $neueSpesen->abwesenheitsstd;
						$spesen->tagegeld = $neueSpesen->tagegeld;
						$spesen->tagegeldko = $neueSpesen->tagegeldko;
						$spesen->uebernachtung = $neueSpesen->uebernachtung; 
						$spesen->uebernachtungko = $neueSpesen->uebernachtungko;
						$spesen->benutzer_persnr = $neueSpesen->benutzer_persnr;
						$spesen->timeStamp = R::$f->now();
						$id = R::store($spesen);
					}
				}
				$sql = 'SELECT s.*,
						b.name as mitarbeiter
					FROM spesen as s,
						benutzer as b
					WHERE s.persnr IN ('.R::genSlots($persnr).')
						AND s.anfdat=?
						AND s.persnr = b.persnr
					ORDER BY anfdat';
				array_push($persnr, $neueSpesen->anfdat);	
				$spesen = R::getAll($sql,$persnr);
				if ($spesen) {
					echo json_encode($spesen, JSON_NUMERIC_CHECK);
				} else {
					echo json_encode([]);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function spesenVon($app) {
		return function () use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$request = $app->request();
				$body = $request->getBody();
				$neueSpesen = json_decode($request->getBody());
				$persnr = array();
				if (!is_array($neueSpesen->persnr)) { 
					array_push($persnr, $neueSpesen->persnr);
				} else {
					$persnr = $neueSpesen->persnr;
				}
				$sql = 'SELECT s.*,
						b.name as mitarbeiter
					FROM spesen as s,
						benutzer as b
					WHERE s.persnr IN ('.R::genSlots($persnr).')
						AND s.anfdat=?
						AND s.persnr = b.persnr
					ORDER BY anfdat';
				array_push($persnr, $neueSpesen->anfdat);	
				$spesen = R::getAll($sql,$persnr);
				if ($spesen) {
					echo json_encode($spesen, JSON_NUMERIC_CHECK);
				} else {
					echo json_encode([]);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function putSpesen($app) {
		return function () use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$request = $app->request();
				$body = $request->getBody();
				$neueSpesen = json_decode($request->getBody());
				$spesen = R::load('spesen',$neueSpesen->id);    
				$spesen->persnr = $neueSpesen->persnr;
				$spesen->anfdat = $neueSpesen->anfdat;
				$spesen->land = $neueSpesen->land;
				$spesen->reisebeginn = $neueSpesen->reisebeginn;
				$spesen->reiseende = $neueSpesen->reiseende;
				$spesen->abwesenheitsstd = $neueSpesen->abwesenheitsstd;
				$spesen->tagegeld = $neueSpesen->tagegeld;
				$spesen->tagegeldko = $neueSpesen->tagegeldko;
				$spesen->uebernachtung = $neueSpesen->uebernachtung; 
				$spesen->uebernachtungko = $neueSpesen->uebernachtungko;
				$spesen->benutzer_persnr = $neueSpesen->benutzer_persnr;
				$spesen->timeStamp = R::$f->now();
				$id = R::store($spesen);
				if ($id) {
					echo json_encode($id, JSON_NUMERIC_CHECK);
				} else {
					echo json_encode([]);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function getSpesen($app) {
		return function ($persnr, $anfDat) use ($app) {
			try {
				$spesen = R::findOne('spesen', 'persnr=? AND anfdat=?', array($persnr,$anfDat));
				// if found, return JSON response
				$app->response()->header('Content-Type', 'application/json');
				// return JSON-encoded response body with query results
				if ($spesen) {
					echo json_encode(R::exportAll($spesen),JSON_NUMERIC_CHECK);
				} else {
					echo json_encode([]);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}

	public function getSpesenVonBis($app) {
		return function ($persnr, $von, $bis) use ($app) {
			try {
				$sql = "SELECT s.*,
						b.name as mitarbeiter
					FROM spesen as s,
						benutzer as b
					WHERE s.benutzer_persnr=?
						AND s.anfdat>=?
						And s.anfdat<=?
						AND s.persnr = b.persnr
					ORDER BY anfdat";
				$spesen = R::getAll($sql,array($persnr, $von, $bis));
			//	$spesen = R::find('spesen', 'benutzer_persnr=? AND anfdat>=? AND anfdat<=?', array($persnr,$von, $bis));
				// if found, return JSON response
				$app->response()->header('Content-Type', 'application/json');
				// return JSON-encoded response body with query results
				if ($spesen) {
					echo json_encode($spesen,JSON_NUMERIC_CHECK);
				} else {
					echo json_encode([]);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}

	
	
	
	
}

?> 