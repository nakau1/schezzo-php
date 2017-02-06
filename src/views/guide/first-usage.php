<?php
/* @var $this \app\views\View */

$this->backAction = ['guide/'];
$this->isGrayBackground = false;
$this->title = 'カードの使い方';
?>
<div class="how_to_head clearfix">
    <p class="how_to_head_text">使えるお店の目印は<br>VISAマーク！</p>
    <p class="how_to_head_read">Pollet Visa Prepaidは、全国のコンビニやスーパーはもちろん、アプリ課金からネットショッピングまで、世界約4000万店舗のVISA加盟店で使えます！</p>
</div>
<div class="main_box card_details_box">
    <h2>お店での利用</h2>
    <div class="text_card_details_box">
        <ul class="list_disc">
            <li>店員さんにカードを渡して、「VISAカードで」と伝えて支払います。</li>
            <li>お店によってはサインを求められることがあります。</li>
            <li>分割払いはできないので、支払回数を聞かれたら「１回で」と伝えてください。</li>
        </ul>
    </div>
    <?= $this->img('img_howto01', ['class' => 'img_howto']) ?>
</div>
<div class="main_box card_details_box mt50">
    <h2>インターネットショッピングでの利用</h2>
    <div class="text_card_details_box">
        <p>お店だけではなく、インターネットでのお買い物でも使えます。
        <p class="mt10">クレジットカード払いを選択して、カード番号や有効期限を入力してください。 カード名義人の名前は、自分の名前をローマ字で入力してください。（例：「YAMADA TARO」）
        <p class="text_ss">※苗字名前の順番はどちらでも構いません</p>
    </div>
    <?= $this->img('img_howto02', ['class' => 'img_howto mb50']) ?>
</div>