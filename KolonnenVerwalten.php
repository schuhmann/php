<?php
class KolonnenVerwalten
{
	public function getAlleKolonnen($app) {
		return function () use ($app) {
			try {
				$user = R::getAll('select * from kolonnen'); 
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

	
	public function deleteKolonne($app) {
		return function ($id) use ($app) {
			try {
				$post = R::load('kolonnen',$id); //Retrieve
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
	
	public function neueKolonne($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$neueKolonne = json_decode($request->getBody());
				$kolonne = R::dispense('kolonnen');
				$kolonne->name = $neueKolonne->name;
				$kolonne->land = $neueKolonne->land;
				$kolonne->plz = $neueKolonne->plz;
				$kolonne->ort = $neueKolonne->ort;
				$kolonne->str = $neueKolonne->str;
				$kolonne->tel = $neueKolonne->tel;
				$kolonne->mail = $neueKolonne->mail;
				$kolonne->benutzer = $neueKolonne->benutzer;
				$id = R::store($kolonne);
				$kolonne = R::load('kolonnen', $id);
				if ($id) {
					echo json_encode(R::exportAll($kolonne), JSON_NUMERIC_CHECK);
				}
			} catch (Exception $e) {
				$app->response()->status(400);
				$app->response()->header('X-Status-Reason', $e->getMessage());
			}
		};
	}
	
	public function putKolonne($app) {
		return function () use ($app) {
			try {
				$request = $app->request();
				$body = $request->getBody();
				$neueKolonne = json_decode($request->getBody());
				$kolonne = R::load('kolonnen', $neueKolonne->id);
				$kolonne->name = $neueKolonne->name;
				$kolonne->land = $neueKolonne->land;
				$kolonne->plz = $neueKolonne->plz;
				$kolonne->ort = $neueKolonne->ort;
				$kolonne->str = $neueKolonne->str;
				$kolonne->tel = $neueKolonne->tel;
				$kolonne->mail = $neueKolonne->mail;
				$kolonne->benutzer = $neueKolonne->benutzer;
				$id = R::store($kolonne);
				$kolonne = R::load('kolonnen', $id);
				if ($id) {
					echo json_encode(R::exportAll($kolonne), JSON_NUMERIC_CHECK);
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