<?php
class ZeitenMontage
{
	public function getZeiten($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$query = json_decode($body);
				$benutzer_persnr = $query->benutzer_persnr;
				$res = (object) null;
				/*
				$arrSql = array();
				//Wenn die Baunummer da ist
				if (isset($query->baunr)) { 
					array_push($arrSql, $query->baunr);
					$whereBaunr = 'z.baunr =? AND ';
					$sql = "SELECT sum(std) FROM zeitenmontage WHERE baunr =? AND lohnart IN (11,12) GROUP BY baunr";
					$res->gesamt = R::getCell($sql,array($query->baunr));
				
				} else {
					$whereBaunr = '';
					$res->gesamt = 0;
				}
				array_push($arrSql, $query->von);
				array_push($arrSql, $query->bis);
				//foreach ($query->lohnart as $row) {
				//	array_push($arrSql, $row);
				//}
				array_push($arrSql, $query->benutzer_persnr);
				*/
				$sql = 
				'SELECT z.id, z.persnr, b.name as mitarbeiter, z.baunr, z.anfdat,
					z.lohnart, l.name as taetigkeit, z.timevon, z.timebis, z.std, z.pause
				FROM zeitenmontage as z, benutzer as b, lohnarten as l
				WHERE anfdat>=? AND anfdat<=? AND z.persnr = b.persnr
					AND z.lohnart = l.id
					AND z.benutzer_persnr =?
				ORDER BY timevon';
				$zeiten = R::getAll($sql, array($query->von, $query->bis, $query->benutzer_persnr));
				$sql = 
				'SELECT DISTINCT persnr
				FROM zeitenmontage
				WHERE anfdat>=? AND anfdat<=? AND benutzer_persnr =?';
				$persnr = R::getCol($sql, array($query->von, $query->bis, $query->benutzer_persnr));
				$arrbv  = array();
				foreach ($zeiten as $row) { 
					$bv = (object) null;
					$bauvorhaben = R::getRow('select * from bauvorhaben where baunr=?', array($row['baunr']));
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
				$res->zeiten = $arrbv;
    			//Wenn Zeiten da sind dann Spesen suchen
				if (count($persnr) > 0) {
					$sql = 'SELECT persnr, name
						FROM benutzer 
						WHERE persnr IN ('.R::genSlots($persnr).')';
					$res->persnr = R::getAll($sql,$persnr);
					$sql = '
						SELECT s.*,	b.name as mitarbeiter
						FROM spesen as s, benutzer as b
						WHERE s.persnr IN ('.R::genSlots($persnr).') AND s.anfdat>=? AND s.anfdat<=?
							AND s.persnr = b.persnr
						ORDER BY anfdat';
					array_push($persnr, $query->von);
					array_push($persnr, $query->bis);
					$spesen = R::getAll($sql,$persnr);
					$res->spesen = $spesen;
				} else {
					$res->spesen = array();
					$res->persnr = $persnr;
				} 
				echo json_encode($res,JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public static function testZeiten($neueZeit) {
		$id = 0;
		$sql = 
			"SELECT z.id, z.persnr, z.anfdat, z.lohnart, z.timevon, z.timebis, z.std, z.pause
			FROM zeitenmontage as z
			WHERE anfdat =?
				AND z.persnr =?
				AND z.timebis > ?
				AND z.timevon < ?";
		$zeit = R::getRow($sql, array($neueZeit->anfdat,$neueZeit->persnr,$neueZeit->timevon,$neueZeit->timebis));
		if ($zeit) {
			$id = $zeit['id'];
		}
		return $id;
 	}
	
	public function putZeiten($app) {
		return function () use ($app) {
			try {
				$gesamt = 0;
				$r = null;
				$request = $app->request();
				$neueZeiten = json_decode($request->getBody());
				$arrRes = array();
				$arrFehler = array();
				$sql = 
					'SELECT z.id, z.persnr, b.name as mitarbeiter, z.baunr, z.anfdat,
						z.lohnart, l.name as taetigkeit, z.timevon, z.timebis, z.std, z.pause, z.benutzer_persnr
					FROM zeitenmontage as z, benutzer as b, lohnarten as l
					WHERE z.persnr = b.persnr
						AND z.lohnart = l.id AND z.persnr = b.persnr
						AND z.id =?
					ORDER BY timevon';
				if (is_array($neueZeiten) || (!empty($neueZeiten))) {
					foreach ($neueZeiten as $neueZeit) {
						$fehler = $this->testZeiten($neueZeit);
						if ($fehler == 0) {
							$zeit = R::dispense('zeitenmontage');
							$zeit->persnr = $neueZeit->persnr;
							$zeit->baunr = ($neueZeit->baunr) ? $neueZeit->baunr : null;
							$zeit->anfdat = $neueZeit->anfdat;
							$zeit->lohnart = $neueZeit->lohnart;
							$zeit->timevon = $neueZeit->timevon;
							$zeit->timebis = $neueZeit->timebis;
							$zeit->std = $neueZeit->std;
							$zeit->pause = $neueZeit->pause;
							$zeit->benutzer_persnr = $neueZeit->benutzer_persnr;
							$zeit->timeStamp = R::$f->now();
							$id = R::store($zeit);
							$mitBaunr = R::getRow($sql, array($id));
							$bauvorhaben = R::findOne('bauvorhaben', 'baunr=?', array($mitBaunr['baunr']));
							$arrNeueZeit = (object) array_merge( (array)$mitBaunr, array( 'bauherr' => $bauvorhaben->name));
							array_push($arrRes, $arrNeueZeit);
						} else {
							$mitBaunr = R::getRow($sql, array($fehler));
							$bauvorhaben = R::findOne('bauvorhaben', 'baunr=?', array($mitBaunr['baunr']));
							$arrNeueZeit = (object) array_merge( (array)$mitBaunr, array( 'bauherr' => $bauvorhaben->name));
							array_push($arrFehler, $arrNeueZeit);
						}
					}
				}
				$sql = "SELECT sum(std)
					FROM zeitenmontage
					WHERE baunr =?
					AND lohnart IN (11,12)
					GROUP BY baunr";
				$r->gesamt = R::getCell($sql,array($neueZeit->baunr));
				$r->id = $id;
				$r->neueZeiten = $arrRes;
				$r->fehler = $arrFehler;
				//if ($id) {
					echo json_encode($r, JSON_NUMERIC_CHECK);
				//} else {
				//	echo json_encode([]);
				//}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
    }
	
	public function getLohnZeiten($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$query = json_decode($body);
				$arrSql = array();
				foreach ($query->persnr as $row) {
					array_push($arrSql, $row);
				}
				array_push($arrSql, $query->von);
				array_push($arrSql, $query->bis);
				$sql = 
				'SELECT z.id, z.persnr, b.name as mitarbeiter, z.baunr, z.anfdat,
					z.lohnart, l.name as taetigkeit, z.timevon, z.timebis, z.std, z.pause
				FROM zeitenmontage as z, benutzer as b, lohnarten as l
				WHERE z.persnr IN ('.R::genSlots($query->persnr).')
					AND z.anfdat>=?
					AND z.anfdat<=?
					AND z.persnr = b.persnr
					AND z.lohnart = l.id
				ORDER BY timevon';
				$zeiten = R::getAll($sql, $arrSql);
				$arrbv = array();
				$persnr = array();
				foreach ($zeiten as $row) { 
					$bv = null;
					$bauvorhaben = R::findOne('bauvorhaben', 'baunr=?', array($row['baunr']));
					$bv->id = $row['id'];
					$bv->persnr = $row['persnr'];
					if (!in_array($persnr, $row['persnr'])) {
						array_push($persnr, $row['persnr']);
					}
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
				$res->zeiten = $arrbv;
				//Wenn Zeiten da sind dann Spesen suchen
				if (count($persnr) > 0) {
					$sql = 'SELECT persnr, name
						FROM benutzer 
						WHERE persnr IN ('.R::genSlots($persnr).')';
					$res->persnr = R::getAll($sql,$persnr);
					$sql = '
						SELECT s.*,	b.name as mitarbeiter
						FROM spesen as s, benutzer as b
						WHERE s.persnr IN ('.R::genSlots($persnr).') AND s.anfdat>=? AND s.anfdat<=?
							AND s.persnr = b.persnr
						ORDER BY anfdat';
					array_push($persnr, $query->von);
					array_push($persnr, $query->bis);
					$spesen = R::getAll($sql,$persnr);
					$res->spesen = $spesen;
				} else {
					$res->spesen = array();
					$res->persnr = array();
				} 
				echo json_encode($res, JSON_NUMERIC_CHECK);
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
}

?> 