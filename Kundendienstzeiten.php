<?php
class Kundendienstzeiten
{
	public function zeitDeleteKd($app) {
		return function ($id) use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$post = R::load('zeitenmontage',$id); //Retrieve
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
	
		public function neueZeitKd($app) {
		return function () use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$request = $app->request();
				$body = $request->getBody();
				$neueZeit = json_decode($request->getBody());
				$zeit = R::dispense('zeitenmontage');
				$zeit->persnr = $neueZeit->persnr;
				$zeit->baunr = ($neueZeit->baunr === "") ? null : $neueZeit->baunr;
				$zeit->anfdat = $neueZeit->anfdat;
				$zeit->lohnart = $neueZeit->lohnart;
				$zeit->timevon = $neueZeit->timevon;
				$zeit->timebis = $neueZeit->timebis;
				$zeit->std = $neueZeit->std;
				$zeit->pause = $neueZeit->pause;
				$zeit->benutzer_persnr = $neueZeit->benutzer_persnr;
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
	
	public function getZeitKd($app) {
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
						z.pause
					FROM zeitenmontage as z,
						lohnarten as l,
						benutzer as b,
						bauvorhaben as h
					WHERE z.persnr=?
						AND z.anfDat=?
						AND z.baunr = h.baunr
						AND z.baunr IS NOT NULL
						AND z.persnr = b.persnr
						AND z.lohnart = l.id
					ORDER BY timevon";
				$r->gesamt = 0;
				$mitBaunr = R::getAll($sql,array($persnr, $anfDat));
				$sql = "SELECT z.id,
						z.persnr,
						b.name as mitarbeiter,
						z.baunr,
						z.anfdat,
						z.lohnart,
						l.name as taetigkeit,
						z.timevon,
						z.timebis,
						z.std,
						z.pause
					FROM zeitenmontage as z,
						lohnarten as l,
						benutzer as b
					WHERE z.persnr=?
						AND z.anfDat=?
						AND z.baunr IS NULL
						AND z.persnr = b.persnr
						AND z.lohnart = l.id
					ORDER BY timevon";
				$ohneBaunr = R::getAll($sql,array($persnr, $anfDat));
				$r->zeiten = array_merge($ohneBaunr, $mitBaunr);
				//$arrbv  = array();
				/*
				foreach ($r->zeiten as $row) { 
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
				$r->zeiten = $arrbv;
				*/
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
	public function getZeitAlleVonBisKd($app) {
		return function ($persnr, $von, $bis) use ($app) {
			try {
				$sql = "SELECT z.id,
						z.persnr,
						b.name as mitarbeiter,
						z.baunr,
						z.anfdat,
						z.lohnart,
						l.name as taetigkeit,
						z.timevon,
						z.timebis,
						z.std,
						z.pause
					FROM zeitenmontage as z,
						lohnarten as l,
						benutzer as b
					WHERE z.persnr=?
						AND z.anfdat>=?
						And z.anfdat<=?
						AND z.persnr = b.persnr
						AND z.lohnart = l.id
					ORDER BY anfdat, timevon";
				$zeiten = R::getAll($sql,array($persnr, $von, $bis));
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
					array_push($arrbv, $bv);
				}
				$zeiten = $arrbv;
				// if found, return JSON response
				$app->response()->header('Content-Type', 'application/json');
				// return JSON-encoded response body with query results
				echo json_encode($zeiten,JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
}

?> 