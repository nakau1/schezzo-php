<?php
/* @var $this \app\views\View */

$this->backAction = ['guide/'];
$this->isGrayBackground = false;
$this->title = 'チャージについて';
?>
<div class="main_box card_details_box">
    <h2>チャージについて</h2>
    <div class="text_card_details_box">
        <p><strong>チャージ方法</strong></p>
        <div class="img_card_details_box"><?= $this->img('img_card_details04', ['class' => 'card_details04']) ?></div>
        <p>チャージには、ハピタス等のポイントサイトのポイント交換（今後も追加予定）の他、クレジットカードを使ったクレジットチャージ、インターネットバンキングのペイジーチャージの3通りあります。<br>
            ポイントのチャージはpolletアプリまたは各ポイントサイトのポイント交換から、クレジットカード・ペイジーチャージはpollet会員専用サイトから行えます。</p>
        <p class="mt40"><strong>チャージの反映時間</strong></p>
        <table class="card_details_table">
            <tr>
                <th>チャージ方法</th>
                <th>反映時間</th>
            </tr>
            <tr>
                <td>ポイントサイト</td>
                <td>最大1時間～各ポイントサイトの交換タイミング</td>
            </tr>
            <tr>
                <td>クレジットカード</td>
                <td>リアルタイム</td>
            </tr>
            <tr>
                <td>ペイジー</td>
                <td>リアルタイム</td>
            </tr>
        </table>
        <p class="mt40"><strong>チャージ金額の上限</strong></p>
        <p class="mt10">また、1回当たりのチャージ上限は50万円、カード残高の上限は100万円までです。<br>
            なお、ポイントサイトからのポイント交換の場合、各ポイントサイトが設定している上限を超えることはできません。 </p>
    </div>
</div>