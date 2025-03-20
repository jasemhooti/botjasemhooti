<?php
require_once 'config.php';

class GameHandler {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function createGame($playerId, $bet) {
        $gameId = uniqid();
        $stmt = $this->conn->prepare("INSERT INTO games (game_id, player1_id, bet_amount) VALUES (?, ?, ?)");
        $stmt->execute([$gameId, $playerId, $bet]);
        return $gameId;
    }
    
    public function handleChoice($gameId, $playerId, $choice, $isPlayer1) {
        $column = $isPlayer1 ? 'player1_choice' : 'player2_choice';
        $stmt = $this->conn->prepare("UPDATE games SET $column = ? WHERE game_id = ?");
        $stmt->execute([$choice, $gameId]);
    }
    
    public function checkWinner($gameId) {
        $stmt = $this->conn->prepare("SELECT * FROM games WHERE game_id = ?");
        $stmt->execute([$gameId]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($game['player1_choice'] && $game['player2_choice']) {
            $winningNumber = rand(1, 6);
            $winner = null;
            
            if($game['player1_choice'] == $winningNumber) $winner = $game['player1_id'];
            if($game['player2_choice'] == $winningNumber) $winner = $game['player2_id'];
            
            // Update game status
            $this->conn->prepare("UPDATE games SET status = 'completed', winning_number = ? WHERE game_id = ?")
                ->execute([$winningNumber, $gameId]);
            
            return $winner;
        }
        return null;
    }
}
?>
