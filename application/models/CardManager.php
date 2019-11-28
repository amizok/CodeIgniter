<?php
class CardManager extends CI_Model {

	public static $TRUMP;
	public static $DAIFUGO;
	public static $GAME_NAME = 'DFG';

	public function __construct() {
		$this->load->helper('url_helper');
		
		//TODO: database.phpを直す
		CardManager::$TRUMP = $this->load->database('default',true);
		CardManager::$DAIFUGO = $this->load->database('daifugo', true);
	}

	/**
	 * Get first player's hands.
	 * Insert 'daifugo_hand' DB.
	 * 
	 * @param int $playerNum
	 * @return Array $allHandLists 
	 * 			[0] : user, [1~$playerNum]: cpu
	 * 			[0] => Array (
	 *				[0] => Array ( [37] => assets/img/cards/heart_11.png )
	 * 				[1] => Array ( [26] => assets/img/cards/diamond_13.png )...
	 * 				[n] => Array ( {card id} => {card img path})
	 *			)...
	 */
	public function getFirstHandsLists($playerNum) {
		//全カードを順番にListに詰める
		//[0]=>1 [1]=>2 [2]=>3...
		$cardsInOrder = array();
		$query = CardManager::$DAIFUGO->query('SELECT card_id FROM ms_trump_card');
		foreach ($query->result() as $row) {
			array_push($cardsInOrder, $row->card_id);
		}

		//ランダムに並べ変えてプレイヤーごとに手札をListにする
		$allPlayerCardsList = array();
		$selectedCard = array();
		for ($i = 0; $i < $playerNum; $i++) {
			//一人分のカードを格納するリスト
			$singleCards = array();
			$cnt = 0;
			while ($cnt < floor(54/$playerNum)) {
				$randomIndex = rand(0, 53);

				if (count($selectedCard) == 0) {//1回目
					//ランダムで選んだcard_id => $cardsInOrder[$randomIndex]
					$id = $cardsInOrder[$randomIndex];
					array_push($singleCards, $id);
					array_push($selectedCard, $randomIndex);
					$cnt++;
				} else {//2回目以降
					//すでに選んだカードと被らないように判定する
					$IsSelectedCard = in_array($randomIndex, $selectedCard);
					if (!$IsSelectedCard) {//すでに選んだカードと被ってなかったら
						$id = $cardsInOrder[$randomIndex];
						array_push($singleCards, $id);
						array_push($selectedCard, $randomIndex);
						$cnt++;
					}
				}
			}
			array_push($allPlayerCardsList, $singleCards);
		};

		//余りがある場合は余りをランダムにプレイヤーに振り分ける
		if (54%$playerNum > 0) {
			//余ったカードをListにつめる
			$restCardList = array();
			for ($i = 0; $i < 54; $i++) {
				$isContained = in_array($i, $selectedCard);
				if (!$isContained) {
					array_push($restCardList, $cardsInOrder[$i]);
				}
			}

			$selectedPlayers = array();
			$cnt = 0;
			$cardIndex = floor(54/$playerNum);
			while ($cnt < count($restCardList)) {
				$randomPlayerIndex = rand(0, ($playerNum - 1));

				if (count($selectedPlayers) == 0) {//1回目
					$id = $restCardList[$cnt];
					array_push($allPlayerCardsList[$randomPlayerIndex], $id);
					array_push($selectedPlayers, $randomPlayerIndex);
					$cnt++;
					$cardIndex++;
				} else {//2回目以降
					$IsfoundSameNo = in_array($randomPlayerIndex, $selectedPlayers);
					if (!$IsfoundSameNo) {
						$id = $restCardList[$cnt];
						array_push($allPlayerCardsList[$randomPlayerIndex], $id);
						array_push($selectedPlayers, $randomPlayerIndex);
						$cnt++;
						$cardIndex++;
					}
				}
			}
		}
		//TODO output hand list($allPlayerCardsList) log

		//insert DB
		//TODO: user id(from session?)
		$userId = 'user0';
		$gameId = CardManager::$DAIFUGO->get_where('user_playing_game', array('user_id' => $userId))->row()->playing_game_id;
		$userIdArray = CardManager::$DAIFUGO->get_where('daifugo_matching', array('game_id' => $gameId))->result_array();
		foreach ($allPlayerCardsList as $playerIndex => $playerHandArray) {
			$playerId = $userIdArray[$playerIndex]['user_id'];
			print_r($playerId, true);
			foreach ($playerHandArray as $key => $id) {
				$cardData = array(
					'game_id' => $gameId,
					'user_id' => $playerId,
					'card_id' => $id,
					'used_flg' => false
				);
				CardManager::$DAIFUGO->insert('daifugo_hand', $cardData);
			}
		}
		//TODO output result of insert DB log

		//convert id array to id & card img path array;
		//TODO order card
		$imgPathListOfHands = array();
		for ($i = 0; $i < $playerNum; $i++) {
			$handQuery = CardManager::$DAIFUGO->get_where(
					'daifugo_hand', array('user_id' => $userIdArray[$i]['user_id']));
			$singleHand = array();
			foreach ($handQuery->result() as $handRow) {
				$cardId = $handRow->card_id;
				$cardName = CardManager::$DAIFUGO->get_where('ms_trump_card', array('card_id' => $cardId))->row()->card_name;
				$idPath = array($cardId => 'assets/img/cards/'.$cardName.'.png');
				array_push($singleHand, $idPath);
			}
			array_push($imgPathListOfHands, $singleHand);
		}
		return $imgPathListOfHands;
	}


	/**
	 * Return img path of card's back.
	 * @return String path of card's back
	 */
	public function getCardBack() {
		return 'assets/img/cards/back.png';
	}

	/**
	 * update hand as used(used_flg = true)
	 */
	public function useCard($userId, $selectingCards) {
		$table = '';
		$gameId = CardManager::$DAIFUGO->get_where('user_playing_game', array('user_id' => $userId))->row()->playing_game_id;
		if (strpos($gameId, CardManager::$GAME_NAME) !== false) $table = 'daifugo_hand';
		$idList = explode(',', $selectingCards);
		foreach ($idList as $cardId) {
		 	CardManager::$DAIFUGO->set('used_flg', true);
		 	CardManager::$DAIFUGO->where(array('game_id' => $gameId, 'card_id' => $cardId));
		 	CardManager::$DAIFUGO->update($table);
		 }
	}

	/**
	 * Get all player's hands.
	 * 
	 * @return Array $allHandLists 
	 * 			[0] : user, [1~$playerNum]: cpu
	 * 			[0] => Array (
	 *				[0] => Array ( [37] => assets/img/cards/heart_11.png )
	 * 				[1] => Array ( [26] => assets/img/cards/diamond_13.png )...
	 * 				[n] => Array ( {card id} => {card img path})
	 *			)...
	 */
	public function getLatestHand($playerNum, $userId) {
		$gameId = CardManager::$DAIFUGO->get_where('user_playing_game', array('user_id' => $userId))->row()->playing_game_id;
		$playerIdArray = CardManager::$DAIFUGO->get_where('daifugo_matching', array('game_id' => $gameId))->result_array();
		$imgPathListOfHands = array();
		for ($i = 0; $i < $playerNum; $i++) {
			$handQuery = CardManager::$DAIFUGO->get_where('daifugo_hand', array('user_id' => $playerIdArray[$i]['user_id'], 'used_flg' => false));
			$singleHand = array();
			foreach ($handQuery->result() as $handRow) {
				$cardId = $handRow->card_id;
				$cardName = CardManager::$DAIFUGO->get_where('ms_trump_card', array('card_id' => $cardId))->row()->card_name;
				$idPath = array($cardId => 'assets/img/cards/'.$cardName.'.png');
				array_push($singleHand, $idPath);
			}
			array_push($imgPathListOfHands, $singleHand);
		}
		return $imgPathListOfHands;
	}

	/**
	 * get used cards
	 * @return used card array
	 * 			[0] => Array (
	 *				[0] => Array ( [37] => assets/img/cards/heart_11.png )
	 * 				[1] => Array ( [26] => assets/img/cards/diamond_13.png )...
	 * 				[n] => Array ( {card id} => {card img path})
	 *			)...
	 */
	public function getUsedCards() {
		$allUsedCards = array();
		//TODO: get user id
		$userId = 'user0';
		$table = '';
		$gameId = CardManager::$DAIFUGO->get_where('user_playing_game', array('user_id' => $userId))->row()->playing_game_id;
		if (strpos($gameId, CardManager::$GAME_NAME) !== false) $table = 'daifugo_game_area_card';

		//card idの連想配列を作る
		$allCardIdsArray = array();
		$singleCardIdArray = array();
		CardManager::$DAIFUGO->select('card_ids');
		$query = CardManager::$DAIFUGO->get_where($table, array('game_id' => $gameId, 'discard_flg' => false));
		foreach ($query->result() as $row) {
			$singleCardIdArray = explode(':', $row->card_ids);
			array_push($allCardIdsArray, $singleCardIdArray);
		}

		//convert card id array to img path array
		$allUsedCards = array();
		foreach ($allCardIdsArray as $key => $cardIdArray) {
			$singleIdArray = array();
			foreach ($cardIdArray as $key => $cardId) {
				$cardName = CardManager::$DAIFUGO->get_where('ms_trump_card', array('card_id' => $cardId))->row()->card_name;
				$idPath = array($cardId => 'assets/img/cards/'.$cardName.'.png');
				array_push($singleIdArray, $idPath);
			}
			array_push($allUsedCards, $singleIdArray);
		}
		return $allUsedCards;
	}
}