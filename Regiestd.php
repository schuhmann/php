<?php
class regiestd
	{
	public function getBvSummeRegieZeiten($app) {
		return function () use ($app) {
		try {
			$rows = R::getAll('SELECT DISTINCT baunr FROM regiestunden');
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
					$bv->vgz = is_null($bauvorhaben['vgz']) ? 0 : $bauvorhaben['vgz'];
					$zeit = R::find('zeitenmontage', 'baunr=? AND lohnart IN (11,12)', array($baunr));
					$summe = R::getRow('SELECT SUM(std) as std
						FROM zeitenmontage
						WHERE baunr=? GROUP BY baunr', array($row['baunr']));
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
	
	public function getAlleRegieZeiten($app) {
		return function ($baunr) use ($app) {
			try {
				$sql = "SELECT r.id,
					r.baunr,
					r.pos,
					r.regiegrund_id,
					r.txt,
					r.abteilung_id,
					r.std,
					r.bezahlt,
					r.gepr
				FROM regiestunden as r
				WHERE r.baunr =?
				ORDER BY pos";
				$zeiten = R::getAll($sql,array($baunr));	
				$app->response()->header('Content-Type', 'application/json');
				echo json_encode($zeiten,JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	} 
	

	public function neueRegieZeit($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$neueZeit = json_decode($request->getBody());
				$zeit = R::dispense('regiestunden');
				$zeit->baunr = $neueZeit->baunr;
				$zeit->pos = $neueZeit->pos;
				$zeit->txt = $neueZeit->txt;
				$zeit->regiegrund_id = $neueZeit->regiegrund_id;
				$zeit->abteilung_id = $neueZeit->abteilung_id;
				$zeit->std = $neueZeit->std;
				$zeit->bezahlt = $neueZeit->bezahlt;
				$zeit->gepr = $neueZeit->gepr;
				$zeit->kolonne = $neueZeit->kolonne;
				$zeit->benutzer_persnr = $neueZeit->benutzer_persnr;
				$zeit->timestamp = R::$f->now();
				$id = R::store($zeit);
				$summe = R::getRow('SELECT SUM(bezahlt) as regiezeit
					FROM regiestunden
					WHERE baunr=? GROUP BY baunr', array($neueZeit->baunr));
				$summeStd = R::getRow('SELECT SUM(std) as std
					FROM zeitenmontage
					WHERE baunr=? AND lohnart IN (11,12)
					GROUP BY baunr', array($neueZeit->baunr));
				$res = array ('id'=>$id,'regiezeit'=>$summe['regiezeit'],'std'=>$summeStd["std"]);
				if ($res) {
					echo json_encode($res, JSON_NUMERIC_CHECK);
				} else {
					echo json_encode([]);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};

	} 

	public function zeitRegieDelete($app) {
		return function ($id) use ($app) {
			try {
				$post = R::load('regiestunden',$id); //Retrieve
				$baunr = $post ->baunr;
				R::trash($post);             //Delete
				$gesamt = 0;
				$zeiten = R::find('regiestunden', 'baunr=?', array($baunr));
				if ($zeiten) {
					foreach ($zeiten as $wert) { 
						$gesamt += $wert->bezahlt;
					}
				}
				$post = R::dispense('zeitenmontage');
				$summeStd = R::getRow('SELECT SUM(std) as std
					FROM zeitenmontage
					WHERE baunr=? AND lohnart IN (11,12)
					GROUP BY baunr', array($baunr));
				array_push($res, $summeStd["std"]);
				$res = array ('id'=>$id,'regiezeit'=>$gesamt,'std'=>$summeStd["std"]);
				if ($res) {
					echo json_encode($res, JSON_NUMERIC_CHECK);
				} else {
					echo json_encode([]);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}

	public function zeitRegiePut($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$neueZeit = json_decode($request->getBody());
				$zeit = R::dispense('regiestunden');
				$zeit->id = $neueZeit->id;
				$zeit->baunr = $neueZeit->baunr;
				$zeit->pos = $neueZeit->pos;
				$zeit->txt = $neueZeit->txt;
				$zeit->abteilung_id = $neueZeit->abteilung_id;
				$zeit->regiegrund_id = $neueZeit->regiegrund_id;
				$zeit->std = $neueZeit->std;
				$zeit->bezahlt = $neueZeit->bezahlt;
				$zeit->gepr = $neueZeit->gepr;
				$zeit->benutzer_persnr = $neueZeit->benutzer_persnr;
				$zeit->timestamp = R::$f->now();
				$id = R::store($zeit);
				$summe = R::getRow('SELECT SUM(bezahlt) as regiezeit
					FROM regiestunden
					WHERE baunr=? GROUP BY baunr', array($neueZeit->baunr));
				$summeStd = R::getRow('SELECT SUM(std) as std
					FROM zeitenmontage
					WHERE baunr=? AND lohnart IN (11,12)
					GROUP BY baunr', array($neueZeit->baunr));
				array_push($id, $summeStd);
				$res = array ('id'=>$neueZeit->id,'regiezeit'=>$summe['regiezeit'],'std'=>$summeStd["std"]);
				if ($res) {
					echo json_encode($res, JSON_NUMERIC_CHECK);
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