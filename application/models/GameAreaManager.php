<?php
class GameAreaManager extends CI_Model {

	public static $GAME_NAME = 'DFG';

	public function __construct() {
		$this->load->helper('url_helper');
		$this->load->database();
	}

	/**
	 * insert game area status (daifugo_game_area_card)
	 */
	public function insertGameAreaStatus($passFlg, $latestGameStatusId, $selectingCards) {
		log_message('debug', 'GameAreaManager::insertGameAreaStatus() Start');
		$gameId = $this->db->get_where('tb_daifugo_game_status', array('id' => $latestGameStatusId))->row()->game_id;
		$turnEndFlg = $this->db->get_where('tb_daifugo_turn_status', array('game_status_id' => $latestGameStatusId))->row()->turn_end_flg;

		if ($passFlg) {
			// case pass(update)
			if ($turnEndFlg) {
				// case turn is end
				$updateData = array(
					'discard_flg' => true,
				);
				$this->db->where(array(
					'game_id' => $gameId,
					'game_status_id' => $latestGameStatusId,
					'discard_flg' => false));
				$this->db->update('tb_daifugo_game_area_card', $updateData);
			}
		} else {
			//case put(insert)
			$selectingCardIds = '';
			foreach ($selectingCards as $cardId) {
				$selectingCardIds .= $cardId;
				$selectingCardIds .= ':';
			}
			$selectingCardIds = substr($selectingCardIds, 0, -1);

			$insertData = array(
				'game_id' => $gameId,
				'game_status_id' => $latestGameStatusId,
				'card_ids' => $selectingCardIds,
				'discard_flg' => false,
			);
			$this->db->insert('tb_daifugo_game_area_card', $insertData);
		}
		log_message('debug', 'GameAreaManager::insertGameAreaStatus() Start');
	}
}
