<?php
/* @var $this yii\web\View */
use app\models\TradingHistory;

/* @var $tradingHistories TradingHistory[] */
?>

<div class="panel-list">

    <div class="list-toolbar isys-list-header trading_history -header" list-id="trading_history"
         list-mode-edit="0" list-mode-appendrow="False" list-name="PMBR004SL01-trading_history">
        <div class="list-header-button cancelValidate list-toolbar-dummy">

        </div>
        <div class="list-header-button cancelValidate list-toolbar-right">
                        <span id="MainContent_ListContent_cList_trading_history_trading_history_DataPager-header"
                              style="display: none;"><span></span>&nbsp;<span> </span>&nbsp;<span
                                class="list-header-current-page">1</span>&nbsp;<span> </span>&nbsp;<span> </span>&nbsp;</span>
        </div>
        <div class="list-header-button cancelValidate list-toolbar-right">
                        <span id="MainContent_ListContent_cList_trading_history_trading_history_DataCount-header"
                              class="list-header-datacount badge">4</span>
        </div>
        <div class="list-header-button cancelValidate list-toolbar-right toolbar-columns">
            <div class="btn-toolbar" list-name="PMBR004SL01-trading_history">
                <div class="btn-group focus-btn-group"></div>
                <div class="btn-group dropdown-btn-group pull-right">
                    <button class="btn btn-info button-tiny" button-type="all">全列表示</button>
                    <button class="btn btn-info button-tiny dropdown-toggle" button-type="sel"
                            data-toggle="dropdown">列選択 <span class="caret"></span></button>
                    <ul class="dropdown-menu table-columns" list-name="PMBR004SL01-trading_history">
                        <li class="checkbox-row" button-type="sel"><input type="checkbox"
                                                                          name="toggle-purchaseDate"
                                                                          id="toggle-purchaseDate"
                                                                          value="purchaseDate" checked="">
                            <label for="toggle-purchaseDate">ご利用日</label></li>
                        <li class="checkbox-row" button-type="sel"><input type="checkbox"
                                                                          name="toggle-procType2"
                                                                          id="toggle-procType2"
                                                                          value="procType2" checked="">
                            <label for="toggle-procType2">取引種別</label></li>
                        <li class="checkbox-row" button-type="sel"><input type="checkbox"
                                                                          name="toggle-cardMskNo"
                                                                          id="toggle-cardMskNo"
                                                                          value="cardMskNo" checked="">
                            <label for="toggle-cardMskNo">カード番号</label></li>
                        <li class="checkbox-row" button-type="sel"><input type="checkbox"
                                                                          name="toggle-merchantName"
                                                                          id="toggle-merchantName"
                                                                          value="merchantName" checked="">
                            <label for="toggle-merchantName">加盟店名称</label></li>
                        <li class="checkbox-row" button-type="sel"><input type="checkbox"
                                                                          name="toggle-procAmount"
                                                                          id="toggle-procAmount"
                                                                          value="procAmount" checked="">
                            <label for="toggle-procAmount">ご利用金額（円）</label></li>
                        <li class="checkbox-row" button-type="sel"><input type="checkbox"
                                                                          name="toggle-dealAmount"
                                                                          id="toggle-dealAmount"
                                                                          value="dealAmount" checked="">
                            <label for="toggle-dealAmount">現地通貨額</label></li>
                        <li class="checkbox-row" button-type="sel"><input type="checkbox"
                                                                          name="toggle-dealCurrencyID"
                                                                          id="toggle-dealCurrencyID"
                                                                          value="dealCurrencyID" checked="">
                            <label for="toggle-dealCurrencyID">現地通貨名</label></li>
                        <li class="checkbox-row" button-type="sel"><input type="checkbox"
                                                                          name="toggle-transBuy"
                                                                          id="toggle-transBuy"
                                                                          value="transBuy" checked="">
                            <label for="toggle-transBuy">換算レート</label></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="table container" list-name="PMBR004SL01-trading_history" style="height: 266px;">
        <div class="table-responsive" data-pattern="priority-columns"
             list-name="PMBR004SL01-trading_history">
            <table class="list-container table isys-list-data trading_history " list-id="trading_history"
                   list-mode-edit="0" list-mode-appendrow="False" list-type="0">
                <thead class="list-container table-head isys-list-data trading_history "
                       list-id="trading_history" list-mode-edit="0">
                <tr class="list-row-header">
                    <th width="180" data-priority="1" col-name="purchaseDate" class="list-header-caption"><a
                            id="MainContent_ListContent_cList_trading_history_purchaseDate" tabindex="-1"
                            href="javascript:__doPostBack('ctl00$ctl00$MainContent$ListContent$cList$trading_history$purchaseDate','')">ご利用日</a><span
                            id="cList_purchaseDate_SortLabel"></span></th>
                    <th data-priority="1" col-name="procType2" class="list-header-caption"><a
                            id="MainContent_ListContent_cList_trading_history_procType2" tabindex="-1"
                            href="javascript:__doPostBack('ctl00$ctl00$MainContent$ListContent$cList$trading_history$procType2','')">取引種別</a><span
                            id="cList_procType2_SortLabel"></span></th>
                    <th data-priority="1" col-name="cardMskNo" class="list-header-caption"><a
                            id="MainContent_ListContent_cList_trading_history_cardMskNo" tabindex="-1"
                            href="javascript:__doPostBack('ctl00$ctl00$MainContent$ListContent$cList$trading_history$cardMskNo','')">カード番号</a><span
                            id="cList_cardMskNo_SortLabel"></span></th>
                    <th data-priority="1" col-name="merchantName" class="list-header-caption"><a
                            id="MainContent_ListContent_cList_trading_history_merchantName" tabindex="-1"
                            href="javascript:__doPostBack('ctl00$ctl00$MainContent$ListContent$cList$trading_history$merchantName','')">加盟店名称</a><span
                            id="cList_merchantName_SortLabel"></span></th>
                    <th data-priority="1" col-name="procAmount" class="list-header-caption"><a
                            id="MainContent_ListContent_cList_trading_history_procAmount" tabindex="-1"
                            href="javascript:__doPostBack('ctl00$ctl00$MainContent$ListContent$cList$trading_history$procAmount','')">ご利用金額（円）</a><span
                            id="cList_procAmount_SortLabel"></span></th>
                    <th data-priority="1" col-name="dealAmount" class="list-header-caption"><a
                            id="MainContent_ListContent_cList_trading_history_dealAmount" tabindex="-1"
                            href="javascript:__doPostBack('ctl00$ctl00$MainContent$ListContent$cList$trading_history$dealAmount','')">現地通貨額</a><span
                            id="cList_dealAmount_SortLabel"></span></th>
                    <th data-priority="1" col-name="dealCurrencyID" class="list-header-caption"><a
                            id="MainContent_ListContent_cList_trading_history_dealCurrencyID" tabindex="-1"
                            href="javascript:__doPostBack('ctl00$ctl00$MainContent$ListContent$cList$trading_history$dealCurrencyID','')">現地通貨名</a><span
                            id="cList_dealCurrencyID_SortLabel"></span></th>
                    <th data-priority="1" col-name="transBuy" class="list-header-caption"><a
                            id="MainContent_ListContent_cList_trading_history_transBuy" tabindex="-1"
                            href="javascript:__doPostBack('ctl00$ctl00$MainContent$ListContent$cList$trading_history$transBuy','')">換算レート</a><span
                            id="cList_transBuy_SortLabel"></span></th>
                </tr>
                </thead>
                <tbody>
                <?php $num = 0; ?>
                <?php foreach ($tradingHistories as $tradingHistory) : ?>
                    <tr class="list-row">
                        <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_2_<?php echo $num; ?>" align="Center"
                            valign="middle" col-name="purchaseDate" col-disp="True"><span
                                id="MainContent_ListContent_cList_trading_history_Lst_purchaseDate_0_Label_Lst_purchaseDate_<?php echo $num; ?>"
                                class=" Label_Lst_purchaseDate  List"><?php echo $tradingHistory->tradingDate->format('Y/m/d'); ?></span><input type="hidden"
                                                                                                                                                name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_purchaseDate$Hidden_Lst_purchaseDate"
                                                                                                                                                id="MainContent_ListContent_cList_trading_history_Lst_purchaseDate_0_Hidden_Lst_purchaseDate_<?php echo $num; ?>"
                                                                                                                                                value="<?php echo $tradingHistory->tradingDate->format('Y/m/d'); ?>">
                        </td>
                        <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_10_<?php echo $num; ?>" align="Left"
                            valign="middle" col-name="procType2" col-disp="True"><span
                                id="MainContent_ListContent_cList_trading_history_Lst_procType2_0_Label_Lst_procType2_<?php echo $num; ?>"
                                class=" Label_Lst_procType2  List">ご利用</span><input type="hidden"
                                                                                    name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_procType2$Hidden_Lst_procType2"
                                                                                    id="MainContent_ListContent_cList_trading_history_Lst_procType2_0_Hidden_Lst_procType2_<?php echo $num; ?>"
                                                                                    value="ご利用"></td>
                        <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_8_<?php echo $num; ?>" align="Left"
                            valign="middle" col-name="cardMskNo" col-disp="True"><span
                                id="MainContent_ListContent_cList_trading_history_Lst_cardMskNo_0_Label_Lst_cardMskNo_<?php echo $num; ?>"
                                class=" Label_Lst_cardMskNo  List">****-****-****-7890</span><input
                                type="hidden"
                                name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_cardMskNo$Hidden_Lst_cardMskNo"
                                id="MainContent_ListContent_cList_trading_history_Lst_cardMskNo_0_Hidden_Lst_cardMskNo_<?php echo $num; ?>"
                                value="****-****-****-7890"></td>
                        <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_3_<?php echo $num; ?>" align="Left"
                            valign="middle" col-name="merchantName" col-disp="True"><span
                                id="MainContent_ListContent_cList_trading_history_Lst_merchantName_0_Label_Lst_merchantName_<?php echo $num; ?>"
                                class=" Label_Lst_merchantName  List"><?php echo $tradingHistory->shop; ?></span><input
                                type="hidden"
                                name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_merchantName$Hidden_Lst_merchantName"
                                id="MainContent_ListContent_cList_trading_history_Lst_merchantName_0_Hidden_Lst_merchantName_<?php echo $num; ?>"
                                value="<?php echo $tradingHistory->shop; ?>"></td>
                        <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_6_<?php echo $num; ?>" align="Right"
                            valign="middle" col-name="procAmount" col-disp="True"><span
                                id="MainContent_ListContent_cList_trading_history_Lst_procAmount_0_Label_Lst_procAmount_<?php echo $num; ?>"
                                class=" Label_Lst_procAmount  List lblRight"><?php echo number_format($tradingHistory->spentValue); ?></span><input type="hidden"
                                                                                                                                                    name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_procAmount$Hidden_Lst_procAmount"
                                                                                                                                                    id="MainContent_ListContent_cList_trading_history_Lst_procAmount_0_Hidden_Lst_procAmount_<?php echo $num; ?>"
                                                                                                                                                    value="<?php echo number_format($tradingHistory->spentValue); ?>">
                        </td>
                        <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_4_<?php echo $num; ?>" align="Right"
                            valign="middle" col-name="dealAmount" col-disp="True"><span
                                id="MainContent_ListContent_cList_trading_history_Lst_dealAmount_0_Label_Lst_dealAmount_<?php echo $num; ?>"
                                class=" Label_Lst_dealAmount  List lblRight"><?php echo number_format($tradingHistory->spentValue, 2); ?></span><input type="hidden"
                                                                                                                                                       name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_dealAmount$Hidden_Lst_dealAmount"
                                                                                                                                                       id="MainContent_ListContent_cList_trading_history_Lst_dealAmount_0_Hidden_Lst_dealAmount_<?php echo $num; ?>"
                                                                                                                                                       value="<?php echo number_format($tradingHistory->spentValue, 2); ?>">
                        </td>
                        <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_5_<?php echo $num; ?>" align="Center"
                            valign="middle" col-name="dealCurrencyID" col-disp="True"><span
                                id="MainContent_ListContent_cList_trading_history_Lst_dealCurrencyID_0_Label_Lst_dealCurrencyID_<?php echo $num; ?>"
                                class=" Label_Lst_dealCurrencyID  List">JPY</span><input type="hidden"
                                                                                         name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_dealCurrencyID$Hidden_Lst_dealCurrencyID"
                                                                                         id="MainContent_ListContent_cList_trading_history_Lst_dealCurrencyID_0_Hidden_Lst_dealCurrencyID_<?php echo $num; ?>"
                                                                                         value="JPY"></td>
                        <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_16_<?php echo $num; ?>" align="Right"
                            valign="middle" col-name="transBuy" col-disp="True"><span
                                id="MainContent_ListContent_cList_trading_history_Lst_transBuy_0_Label_Lst_transBuy_<?php echo $num; ?>"
                                class=" Label_Lst_transBuy  List lblRight"></span><input type="hidden"
                                                                                         name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_transBuy$Hidden_Lst_transBuy"
                                                                                         id="MainContent_ListContent_cList_trading_history_Lst_transBuy_0_Hidden_Lst_transBuy_<?php echo $num; ?>">
                        </td>
                        <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_17_<?php echo $num; ?>" align="Left"
                            valign="middle" col-name="trading_history_number" col-disp="False"
                            style="display: none;"><input type="hidden"
                                                          name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_trading_history_number$HiddenField"
                                                          id="MainContent_ListContent_cList_trading_history_Lst_trading_history_number_0_HiddenField_<?php echo $num; ?>"
                                                          value="376"><input type="hidden"
                                                                             name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl0$Lst_RecodeIdentifier"
                                                                             id="MainContent_ListContent_cList_trading_history_Lst_RecodeIdentifier_<?php echo $num; ?>"
                                                                             value="1f4e111c-abdf-4121-abfb-044b98548d35">
                        </td>
                    </tr>
                    <?php $num++; ?>
                <?php endforeach; ?>

                <tr class="list-row list-row-alternating">
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_2_<?php echo $num; ?>" align="Center"
                        valign="middle" col-name="purchaseDate" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_purchaseDate_1_Label_Lst_purchaseDate_<?php echo $num; ?>"
                            class=" Label_Lst_purchaseDate  List">2014/01/01</span><input type="hidden"
                                                                                          name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_purchaseDate$Hidden_Lst_purchaseDate"
                                                                                          id="MainContent_ListContent_cList_trading_history_Lst_purchaseDate_1_Hidden_Lst_purchaseDate_<?php echo $num; ?>"
                                                                                          value="2014/01/01">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_10_<?php echo $num; ?>" align="Left"
                        valign="middle" col-name="procType2" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_procType2_1_Label_Lst_procType2_<?php echo $num; ?>"
                            class=" Label_Lst_procType2  List">カード状態変更</span><input type="hidden"
                                                                                    name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_procType2$Hidden_Lst_procType2"
                                                                                    id="MainContent_ListContent_cList_trading_history_Lst_procType2_1_Hidden_Lst_procType2_<?php echo $num; ?>"
                                                                                    value="カード状態変更"></td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_8_<?php echo $num; ?>" align="Left"
                        valign="middle" col-name="cardMskNo" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_cardMskNo_1_Label_Lst_cardMskNo_<?php echo $num; ?>"
                            class=" Label_Lst_cardMskNo  List">****-****-****-7890</span><input
                            type="hidden"
                            name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_cardMskNo$Hidden_Lst_cardMskNo"
                            id="MainContent_ListContent_cList_trading_history_Lst_cardMskNo_1_Hidden_Lst_cardMskNo_<?php echo $num; ?>"
                            value="****-****-****-7890"></td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_3_<?php echo $num; ?>" align="Left"
                        valign="middle" col-name="merchantName" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_merchantName_1_Label_Lst_merchantName_<?php echo $num; ?>"
                            class=" Label_Lst_merchantName  List"></span><input type="hidden"
                                                                                name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_merchantName$Hidden_Lst_merchantName"
                                                                                id="MainContent_ListContent_cList_trading_history_Lst_merchantName_1_Hidden_Lst_merchantName_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_6_<?php echo $num; ?>" align="Right"
                        valign="middle" col-name="procAmount" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_procAmount_1_Label_Lst_procAmount_<?php echo $num; ?>"
                            class=" Label_Lst_procAmount  List lblRight"></span><input type="hidden"
                                                                                       name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_procAmount$Hidden_Lst_procAmount"
                                                                                       id="MainContent_ListContent_cList_trading_history_Lst_procAmount_1_Hidden_Lst_procAmount_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_4_<?php echo $num; ?>" align="Right"
                        valign="middle" col-name="dealAmount" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_dealAmount_1_Label_Lst_dealAmount_<?php echo $num; ?>"
                            class=" Label_Lst_dealAmount  List lblRight"></span><input type="hidden"
                                                                                       name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_dealAmount$Hidden_Lst_dealAmount"
                                                                                       id="MainContent_ListContent_cList_trading_history_Lst_dealAmount_1_Hidden_Lst_dealAmount_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_5_<?php echo $num; ?>" align="Center"
                        valign="middle" col-name="dealCurrencyID" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_dealCurrencyID_1_Label_Lst_dealCurrencyID_<?php echo $num; ?>"
                            class=" Label_Lst_dealCurrencyID  List"></span><input type="hidden"
                                                                                  name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_dealCurrencyID$Hidden_Lst_dealCurrencyID"
                                                                                  id="MainContent_ListContent_cList_trading_history_Lst_dealCurrencyID_1_Hidden_Lst_dealCurrencyID_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_16_<?php echo $num; ?>" align="Right"
                        valign="middle" col-name="transBuy" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_transBuy_1_Label_Lst_transBuy_<?php echo $num; ?>"
                            class=" Label_Lst_transBuy  List lblRight"></span><input type="hidden"
                                                                                     name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_transBuy$Hidden_Lst_transBuy"
                                                                                     id="MainContent_ListContent_cList_trading_history_Lst_transBuy_1_Hidden_Lst_transBuy_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_17_<?php echo $num; ?>" align="Left"
                        valign="middle" col-name="trading_history_number" col-disp="False"
                        style="display: none;"><input type="hidden"
                                                      name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_trading_history_number$HiddenField"
                                                      id="MainContent_ListContent_cList_trading_history_Lst_trading_history_number_1_HiddenField_<?php echo $num; ?>"
                                                      value="245"><input type="hidden"
                                                                         name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl1$Lst_RecodeIdentifier"
                                                                         id="MainContent_ListContent_cList_trading_history_Lst_RecodeIdentifier_<?php echo $num; ?>"
                                                                         value="3f5bc316-2d77-4a1c-a4e0-60eaac031111">
                    </td>
                </tr>
                <?php $num++; ?>
                <tr class="list-row list-row-alternating">
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_2_<?php echo $num; ?>" align="Center"
                        valign="middle" col-name="purchaseDate" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_purchaseDate_3_Label_Lst_purchaseDate_<?php echo $num; ?>"
                            class=" Label_Lst_purchaseDate  List"></span><input type="hidden"
                                                                                name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_purchaseDate$Hidden_Lst_purchaseDate"
                                                                                id="MainContent_ListContent_cList_trading_history_Lst_purchaseDate_3_Hidden_Lst_purchaseDate_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_10_<?php echo $num; ?>" align="Left"
                        valign="middle" col-name="procType2" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_procType2_3_Label_Lst_procType2_<?php echo $num; ?>"
                            class=" Label_Lst_procType2  List">カード発行</span><input type="hidden"
                                                                                  name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_procType2$Hidden_Lst_procType2"
                                                                                  id="MainContent_ListContent_cList_trading_history_Lst_procType2_3_Hidden_Lst_procType2_<?php echo $num; ?>"
                                                                                  value="カード発行"></td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_8_<?php echo $num; ?>" align="Left"
                        valign="middle" col-name="cardMskNo" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_cardMskNo_3_Label_Lst_cardMskNo_<?php echo $num; ?>"
                            class=" Label_Lst_cardMskNo  List">****-****-****-7890</span><input
                            type="hidden"
                            name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_cardMskNo$Hidden_Lst_cardMskNo"
                            id="MainContent_ListContent_cList_trading_history_Lst_cardMskNo_3_Hidden_Lst_cardMskNo_<?php echo $num; ?>"
                            value="****-****-****-7890"></td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_3_<?php echo $num; ?>" align="Left"
                        valign="middle" col-name="merchantName" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_merchantName_3_Label_Lst_merchantName_<?php echo $num; ?>"
                            class=" Label_Lst_merchantName  List"></span><input type="hidden"
                                                                                name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_merchantName$Hidden_Lst_merchantName"
                                                                                id="MainContent_ListContent_cList_trading_history_Lst_merchantName_3_Hidden_Lst_merchantName_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_6_<?php echo $num; ?>" align="Right"
                        valign="middle" col-name="procAmount" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_procAmount_3_Label_Lst_procAmount_<?php echo $num; ?>"
                            class=" Label_Lst_procAmount  List lblRight"></span><input type="hidden"
                                                                                       name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_procAmount$Hidden_Lst_procAmount"
                                                                                       id="MainContent_ListContent_cList_trading_history_Lst_procAmount_3_Hidden_Lst_procAmount_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_4_<?php echo $num; ?>" align="Right"
                        valign="middle" col-name="dealAmount" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_dealAmount_3_Label_Lst_dealAmount_<?php echo $num; ?>"
                            class=" Label_Lst_dealAmount  List lblRight"></span><input type="hidden"
                                                                                       name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_dealAmount$Hidden_Lst_dealAmount"
                                                                                       id="MainContent_ListContent_cList_trading_history_Lst_dealAmount_3_Hidden_Lst_dealAmount_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_5_<?php echo $num; ?>" align="Center"
                        valign="middle" col-name="dealCurrencyID" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_dealCurrencyID_3_Label_Lst_dealCurrencyID_<?php echo $num; ?>"
                            class=" Label_Lst_dealCurrencyID  List"></span><input type="hidden"
                                                                                  name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_dealCurrencyID$Hidden_Lst_dealCurrencyID"
                                                                                  id="MainContent_ListContent_cList_trading_history_Lst_dealCurrencyID_3_Hidden_Lst_dealCurrencyID_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_16_<?php echo $num; ?>" align="Right"
                        valign="middle" col-name="transBuy" col-disp="True"><span
                            id="MainContent_ListContent_cList_trading_history_Lst_transBuy_3_Label_Lst_transBuy_<?php echo $num; ?>"
                            class=" Label_Lst_transBuy  List lblRight"></span><input type="hidden"
                                                                                     name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_transBuy$Hidden_Lst_transBuy"
                                                                                     id="MainContent_ListContent_cList_trading_history_Lst_transBuy_3_Hidden_Lst_transBuy_<?php echo $num; ?>">
                    </td>
                    <td id="MainContent_ListContent_cList_trading_history_MBR004SL01_17_<?php echo $num; ?>" align="Left"
                        valign="middle" col-name="trading_history_number" col-disp="False"
                        style="display: none;"><input type="hidden"
                                                      name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_trading_history_number$HiddenField"
                                                      id="MainContent_ListContent_cList_trading_history_Lst_trading_history_number_3_HiddenField_<?php echo $num; ?>"
                                                      value="205"><input type="hidden"
                                                                         name="ctl00$ctl00$MainContent$ListContent$cList$trading_history$ctrl3$Lst_RecodeIdentifier"
                                                                         id="MainContent_ListContent_cList_trading_history_Lst_RecodeIdentifier_<?php echo $num; ?>"
                                                                         value="e3d79308-b240-4989-81e9-b64b0e57de9d">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 利用履歴がフッタにかぶってしまうのでフッタを下に押し出す -->
<div style="height:1000px;"></div>