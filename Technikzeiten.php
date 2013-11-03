<?php
class Technikzeiten
{
	public function zeitDeleteTechnik($app) {
		return function ($id) use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$post = R::load('zeitentechnik',$id); //Retrieve
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
	
	public function neueZeitTechnik($app) {
		return function () use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$request = $app->request();
				$body = $request->getBody();
				$neueZeit = json_decode($request->getBody());
				$zeit = R::dispense('zeitentechnik');
				$zeit->persnr = $neueZeit->persnr;
				$zeit->mitarbeiter = $neueZeit->mitarbeiter;
				$zeit->baunr = $neueZeit->baunr;
				$zeit->bauherr = $neueZeit->bauherr;
				$zeit->anfdat = $neueZeit->anfdat;
				$zeit->lohnart = $neueZeit->lohnart;
				$zeit->taetigkeit = $neueZeit->taetigkeit;
				$zeit->timevon = $neueZeit->timevon;
				$zeit->timebis = $neueZeit->timebis;
				$zeit->std = $neueZeit->std;
				$zeit->pause = $neueZeit->pause;
				$zeit->bemerkung = $neueZeit->bemerkung;
				$zeit->benutzer = $neueZeit->benutzer;
				$zeit->timeStamp = R::$f->now();
				$id = R::store($zeit);
				$r->gesamt = $gesamt;
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
	
	public function getZeitTechnik($app) {
		return function ($persnr, $anfDat) use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$zeiten = R::find('zeitentechnik', 'persnr=? AND anfDat=? AND lohnart IN (110,120,130,140,150,160,170) ORDER BY timevon', array($persnr, $anfDat));
				if ($zeiten) {
					$r->gesamt = $gesamt;
					$r->zeiten = R::exportAll($zeiten);	
					// if found, return JSON response
					$app->response()->header('Content-Type', 'application/json');
					// return JSON-encoded response body with query results
					echo json_encode($r,JSON_NUMERIC_CHECK);
				} else {
				  // else throw exception
					$r->gesamt = 0;
					$r->zeiten = array();	
					echo json_encode($r,JSON_NUMERIC_CHECK);
				 
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	public function getZeitAlleVonBisTechnik($app) {
		return function ($persnr, $von, $bis) use ($app) {
			try {
				$gesamt = 0;
				$zeiten = R::find('zeitentechnik', 'persnr=? AND anfdat>=? AND anfdat<=? ORDER BY anfdat, timevon', array($persnr, $von, $bis));
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
}

?> 