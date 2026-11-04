<?php
session_start();
include('DBconnect.php');

function getVoteCount($issue_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT 
        SUM(CASE WHEN vote_type = 'upvote' THEN 1 WHEN vote_type = 'downvote' THEN -1 ELSE 0 END) 
        AS score FROM issue_votes WHERE issue_id = ?");
    $stmt->bind_param("i", $issue_id);
    $stmt->execute();
    $stmt->bind_result($score);
    $stmt->fetch();
    $stmt->close();
    return $score ?? 0;
}

if (!isset($_GET['issue_id'])) {
    echo "No issue selected.";
    exit;
}

$issue_id = intval($_GET['issue_id']);
$stmt = $conn->prepare("SELECT * FROM issues WHERE issue_id = ?");
$stmt->bind_param("i", $issue_id);
$stmt->execute();
$result = $stmt->get_result();
$issue = $result->fetch_assoc();
$stmt->close();

if (!$issue) {
    echo "Issue not found.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vote on Issue</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<section id="section1">
    <div class="title">Vote on This Issue</div>

    <div class="issue-box" style="border: 1px solid #ccc; padding: 20px; margin: 20px;">
        <h3><?php echo htmlspecialchars($issue['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($issue['description'])); ?></p>

        <?php if (isset($_SESSION['user_id'])) { ?>
            <div class="vote-buttons" style="display: flex; align-items: center; gap: 10px;">
                <form method="post" action="vote.php">
                    <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>">
                    <input type="hidden" name="vote_type" value="upvote">
                    <button type="submit">⬆️ Upvote</button>
                </form>

                <strong><?php echo getVoteCount($issue['issue_id']); ?></strong> votes

                <form method="post" action="vote.php">
                    <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>">
                    <input type="hidden" name="vote_type" value="downvote">
                    <button type="submit">⬇️ Downvote</button>
                </form>
            </div>
        <?php } else { ?>
            <p><em>Login to vote</em></p>
        <?php } ?>
    </div>
</section>

</body>
</html>
