<?php
class Montagezeiten
{
	public function getZeitenLohn($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$query = json_decode($request->getBody());
				$persnr = $query->persnr;
				$baunr = $query->baunr;
				$von = $query->von;
				$bis = $query->bis;
				$persnrs = $persnr;
				array_push($persnrs, $von);
				array_push($persnrs, $bis);
				$sql = 'SELECT z.id,
						z.persnr,
						b.name as mitarbeiter,
						z.baunr,
						z.anfdat,
						z.lohnart,
						l.name as taetigkeit,
						z.timevon,
						z.timebis,
						z.std,
						z.pause,
						z.tagegeld,
						z.uebernachtung
					FROM zeitenmontage as z,
						lohnarten as l,
						benutzer as b
					WHERE z.persnr IN ('.R::genSlots($persnr).')
						AND z.anfdat>=?
						And z.anfdat<=?
						AND z.persnr = b.persnr
						AND z.lohnart = l.id
					ORDER BY anfdat, timevon';
				$zeiten = R::getAll($sql,$persnrs);
				$arrbv  = array();
				foreach ($zeiten as $row) { 
					$bv = null;
					$bauvorhaben = R::findOne('bauvorhaben', 'baunr=?', array($row['baunr']));
					$bv->id = $row['id'];
					$bv->persnr = $row['persnr'];
					$bv->mitarbeiter = $row['mitarbeiter'];
					$bv->baunr = ($bauvorhaben) ? $bauvorhaben['baunr']: null;
					$bv->bauherr = ($bauvorhaben) ? $bauvorhaben['name']: null;
					$bv->anfdat = $row['anfdat'];
					$bv->lohnart = $row['lohnart'];
					$bv->taetigkeit = $row['taetigkeit'];
					$bv->timevon = $row['timevon'];
					$bv->timebis = $row['timebis'];
					$bv->std = $row['std'];
					$bv->pause = $row['pause'];
					$bv->tagegeld = $row['tagegeld'];
					$bv->uebernachtung = $row['uebernachtung'];
					array_push($arrbv, $bv);
				}
				$zeiten = $arrbv;
				//if(is_numeric($query->persnr)) {
				//	$zeiten = R::find('zeitenmontage', 'persnr=? AND anfdat>=? AND anfdat<=? ORDER BY anfdat, timevon', array($query->persnr, $query->von, $query->bis));
				//}
				echo json_encode($zeiten,JSON_NUMERIC_CHECK);
			//	echo json_encode($zeit,JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	
	public function zeitDelete($app) {
		return function ($id,$baunr) use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$post = R::load('zeitenmontage',$id); //Retrieve
				R::trash($post);             //Delete
				$zeiten = R::find('zeitenmontage', 'baunr=? AND lohnart IN (11,12)', array($baunr));
				$sql = "SELECT sum(std)
					FROM zeitenmontage
					WHERE baunr =?
					AND lohnart IN (11,12)
					GROUP BY baunr";
				$r->gesamt = R::getCell($sql,array($baunr));
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
	
	
	public function getZeit($app) {
		return function ($baunr, $anfDat) use ($app) {
			try {
				$sql = "SELECT z.id,
					z.persnr,
					b.name as mitarbeiter,
					z.baunr,
					h.name as bauherr,
					z.anfdat,
					z.lohnart,
					l.name as taetigkeit,
					z.timevon,
					z.timebis,
					z.std,
					z.pause
				FROM zeitenmontage as z,
					benutzer as b,
					lohnarten as l,
					bauvorhaben as h
				WHERE z.baunr =?
					AND anfDat=?
					AND z.persnr = b.persnr
					AND z.lohnart = l.id
					AND z.baunr = h.baunr
					AND z.baunr IS NOT NULL
					AND lohnart IN (11,12)
				ORDER BY timevon";
				$mitBaunr = R::getAll($sql,array($baunr,$anfDat));
				$sql = "SELECT z.id,
					z.persnr,
					b.name as mitarbeiter,
					z.baunr,
					h.name as bauherr,
					z.anfdat,
					z.lohnart,
					l.name as taetigkeit,
					z.timevon,
					z.timebis,
					z.std,
					z.pause
				FROM zeitenmontage as z,
					benutzer as b,
					lohnarten as l,
					bauvorhaben as h
				WHERE z.baunr =?
					AND anfDat=?
					AND z.persnr = b.persnr
					AND z.lohnart = l.id
					AND z.baunr IS NULL
					AND lohnart IN (11,12)
				ORDER BY timevon";
				$ohneBaunr = R::getAll($sql,array($baunr,$anfDat));
				$r->zeiten = array_merge($ohneBaunr, $mitBaunr);
				$persnr = array();
				foreach ($r->zeiten as $row) {
					if (!in_array($row['persnr'], $persnr)) {
						array_push($persnr, $row['persnr']);
					}
				}
				if (count($persnr) > 0) {
					$sql = 'SELECT persnr, name
						FROM benutzer 
						WHERE persnr IN ('.R::genSlots($persnr).')';
					$r->persnr = R::getAll($sql,$persnr);
					$sql = 'SELECT s.*,
							b.name as mitarbeiter
						FROM spesen as s,
							benutzer as b
						WHERE s.persnr IN ('.R::genSlots($persnr).')
							AND s.anfdat=?
							AND s.persnr = b.persnr
						ORDER BY anfdat';
					array_push($persnr, $anfDat);	
					$spesen = R::getAll($sql,$persnr);
					$r->spesen = $spesen;
				} else {
					$r->spesen = array();
					$r->persnr = array(); 
				} 
				$sql = "SELECT sum(std)
					FROM zeitenmontage
					WHERE baunr =?
					AND lohnart IN (11,12)
					GROUP BY baunr";
				$r->gesamt = R::getCell($sql,array($baunr));
				// if found, return JSON response
				$app->response()->header('Content-Type', 'application/json');
				// return JSON-encoded response body with query results
				echo json_encode($r,JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function getBvBenutzer($app) {
		return function ($username) use ($app) {
			try {
			$rows = R::find('bauvorhaben', 'benutzer REGEXP ?', array('[[:<:]]'.$username.'[[:>:]]'));
			$arrbv  = array();
			foreach ($rows as $row) { 
				$bv = null;
				$bauvorhaben = R::findOne('bauvorhaben', 'baunr=?', array($row['baunr']));
				if ($bauvorhaben) {	
					$bv->baunr = $row['baunr'];
					$bv->name = $bauvorhaben['name'];
					$bv->land = is_null($bauvorhaben['land']) ? '' : $bauvorhaben['land'];
					$bv->plz = is_null($bauvorhaben['plz']) ? '' : $bauvorhaben['plz'];
					$bv->bauort = is_null($bauvorhaben['ort']) ? '' : $bauvorhaben['ort'];
					$bv->bauortstr = is_null($bauvorhaben['str']) ? '' : $bauvorhaben['str'];
					$bv->kolonne = is_null($bauvorhaben['kolonne']) ? '' : $bauvorhaben['kolonne'];
					$bv->monteure = is_null($bauvorhaben['monteure']) ? '' : $bauvorhaben['monteure'];
					$bv->vgz = is_null($bauvorhaben['vgz']) ? 0 : $bauvorhaben['vgz'];
					$zeit = R::find('zeitenmontage', 'baunr=? AND lohnart IN (11,12)', array($baunr));
					$summe = R::getRow('SELECT SUM(std) as std
						FROM zeitenmontage
						WHERE baunr=? AND lohnart IN (11,12) GROUP BY baunr', array($row['baunr']));
					$bv->std = is_null($summe['std']) ? 0 : $summe['std'];
					$summe = R::getRow('SELECT SUM(std) as std
						FROM regiestunden
						WHERE baunr=? GROUP BY baunr', array($row['baunr']));
					$bv->regieStd = is_null($summe['std']) ? 0 : $summe['std'];
					$summe = R::getRow('SELECT SUM(bezahlt) as bezahlt
						FROM regiestunden
						WHERE baunr=? GROUP BY baunr', array($row['baunr']));
					$bv->regieStdBezahlt = is_null($summe['bezahlt']) ? 0 : $summe['bezahlt'];
					$tage = R::getAll('SELECT DISTINCT anfdat FROM zeitenmontage WHERE baunr=? AND lohnart IN (11,12) ORDER BY anfdat', array($row['baunr']));
					$bv->tage  = array();
					foreach ($tage as $tag) { 
						array_push($bv->tage, $tag['anfdat']);
					}
					array_push($arrbv, $bv);
				}
			}
			$response = $app->response();
			$response->header('Content-Type', 'application/json');
			$response->body(json_encode($arrbv, JSON_NUMERIC_CHECK));
		} catch (Exception $e) {
			$app->response()->status(400);
			$app->response()->header('X-Status-Reason', $e->getMessage());
		}
		};
	}
	
	public function getZeitAlle($app) {
		return function ($baunr) use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$zeiten = R::find('zeitenmontage', 'baunr=?', array($baunr));
				if ($zeiten) {
					$sql = "SELECT sum(std)
						FROM zeitenmontage
						WHERE baunr =?
						AND lohnart IN (11,12)
						GROUP BY baunr";
					$r->gesamt = R::getCell($sql,array($baunr));
					$r->zeiten = R::exportAll($zeiten);	
					// if found, return JSON response
					$app->response()->header('Content-Type', 'application/json');
					// return JSON-encoded response body with query results
					echo json_encode($r,JSON_NUMERIC_CHECK);
				} else {
					// else throw exception
					$r->gesamt = 0;
					$r->zeiten = array();
					$app->response()->header('Content-Type', 'application/json');
					echo json_encode([]);
				 
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	public function getZeitAlleVonBis($app) {
		return function ($baunr, $von, $bis) use ($app) {
			try {
				$gesamt = 0;
				$sql = "SELECT z.id,
					z.persnr,
					b.name as mitarbeiter,
					z.baunr,
					h.name as bauherr,
					z.anfdat,
					z.lohnart,
					l.name as taetigkeit,
					z.timevon,
					z.timebis,
					z.std,
					z.pause,
					z.tagegeld,
					z.uebernachtung
				FROM zeitenmontage as z,
					benutzer as b,
					lohnarten as l,
					bauvorhaben as h
				WHERE z.baunr =?
					AND anfdat>=?
					AND anfdat<=?
					AND z.persnr = b.persnr
					AND z.lohnart = l.id
					AND z.baunr = h.baunr
					AND z.lohnart IN (11,12)
				ORDER BY z.anfdat, z.timevon";
				$zeiten = R::getAll($sql,array($baunr, $von, $bis));	
				// if found, return JSON response
				$app->response()->header('Content-Type', 'application/json');
				// return JSON-encoded response body with query results
				echo json_encode($zeiten, JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	// Restarbeiten
	public function getZeitAlleVonBisRest($app) {
		return function ($persnr, $von, $bis) use ($app) {
			try {
				$gesamt = 0;
				$sql = "SELECT z.id,
					z.persnr,
					b.name as mitarbeiter,
					z.baunr,
					h.name as bauherr,
					z.anfdat,
					z.lohnart,
					l.name as taetigkeit,
					z.timevon,
					z.timebis,
					z.std,
					z.pause,
					z.tagegeld,
					z.uebernachtung
				FROM zeitenmontage as z,
					benutzer as b,
					lohnarten as l,
					bauvorhaben as h
				WHERE z.benutzer_persnr=?
					AND anfdat>=?
					AND anfdat<=?
					AND z.persnr = b.persnr
					AND z.lohnart = l.id
					AND z.baunr = h.baunr
					AND z.lohnart IN (41,26)
				ORDER BY z.anfdat, z.timevon";
				$zeiten = R::getAll($sql,array($persnr, $von, $bis));	
				// if found, return JSON response
				$app->response()->header('Content-Type', 'application/json');
				// return JSON-encoded response body with query results
				echo json_encode($zeiten, JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function getZeitRest($app) {
		return function ($persnr, $anfDat) use ($app) {
			try {
				$sql = "SELECT z.id,
						z.persnr,
						b.name as mitarbeiter,
						z.baunr,
						h.name as bauherr,
						z.anfdat,
						z.lohnart,
						l.name as taetigkeit,
						z.timevon,
						z.timebis,
						z.std,
						z.pause,
						z.tagegeld,
						z.uebernachtung
					FROM zeitenmontage as z,
						lohnarten as l,
						bauvorhaben as h,
						benutzer as b
					WHERE z.benutzer_persnr=?
						AND z.anfDat=?
						AND z.persnr = b.persnr
						AND z.lohnart = l.id
						AND z.lohnart IN (41,26)
						AND z.baunr = h.baunr
					ORDER BY z.timevon";
				$r->gesamt = 0;
				$r->zeiten = R::getAll($sql,array($persnr, $anfDat));
				// if found, return JSON response
				$app->response()->header('Content-Type', 'application/json');
				// return JSON-encoded response body with query results
				echo json_encode($r,JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function getAlleZeit($app) {
		return function () use ($app) {
			try {
				$zeiten = R::find('zeitenmontage');
				if ($zeiten) {
					// if found, return JSON response
					$app->response()->header('Content-Type', 'application/json');
					// return JSON-encoded response body with query results
					echo json_encode(R::exportAll($zeiten),JSON_NUMERIC_CHECK);
				} else {
				  // else throw exception
				  echo json_encode([]);
				 
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	public function neueZeit($app) {
		return function () use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$request = $app->request();
				$body = $request->getBody();
				$neueZeit = json_decode($request->getBody());
				$zeit = R::dispense('zeitenmontage');
				$zeit->persnr = $neueZeit->persnr;
				$zeit->baunr = $neueZeit->baunr;
				$zeit->anfdat = $neueZeit->anfdat;
				$zeit->lohnart = $neueZeit->lohnart;
				$zeit->timevon = $neueZeit->timevon;
				$zeit->timebis = $neueZeit->timebis;
				$zeit->std = $neueZeit->std;
				$zeit->pause = $neueZeit->pause;
				$zeit->tagegeld = ($neueZeit->tagegeld === null) ? 0 : $neueZeit->tagegeld;
				$zeit->uebernachtung = ($neueZeit->uebernachtung === null) ? 0 : $neueZeit->uebernachtung;
				$zeit->benutzer_persnr = $neueZeit->benutzer_persnr;
				$zeit->timeStamp = R::$f->now();
				$id = R::store($zeit);
				$zeiten = R::find('zeitenmontage', 'baunr=? AND lohnart IN (11,12)', array($neueZeit->baunr));
				$sql = "SELECT sum(std)
					FROM zeitenmontage
					WHERE baunr =?
					AND lohnart IN (11,12)
					GROUP BY baunr";
				$r->gesamt = R::getCell($sql,array($neueZeit->baunr));
				$r->id = $id;	
				if ($id) {
					echo json_encode($r, JSON_NUMERIC_CHECK);
				} else {
					echo json_encode([]);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	public function getZeitGesamt($app) {
		return function ($baunr) use ($app) {
			try {
				$zeiten = R::find('zeitenmontage', 'baunr=?', array($baunr));
				if ($zeiten) {
					$gesamt = 0;
					// if found, return JSON response
					$app->response()->header('Content-Type', 'application/json');
					// return JSON-encoded response body with query results
					$sql = "SELECT sum(std)
						FROM zeitenmontage
						WHERE baunr =?
						AND lohnart IN (11,12)
						GROUP BY baunr";
						$gesamt = R::getCell($sql,array($baunr));
					echo $gesamt;
					
					//echo json_encode(R::exportAll($zeiten),JSON_NUMERIC_CHECK);
				} else {
				  // else throw exception
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