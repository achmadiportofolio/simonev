<div class="pagination">
    <com:TActivePager ID="pager" OnCallBack="Page.renderCallback" ControlToPaginate="RepeaterS" Mode="Numeric" OnPageIndexChanged="Page.Page_Changed" PrevPageText="&laquo; Previous" NextPageText="Next &raquo;" PageButtonCount="10" FirstPageText="First" LastPageText="Last">	
        <prop:ClientSide.OnPreDispatch>
            $('loadingpager').show();
        </prop:ClientSide.OnPreDispatch>					
        <prop:ClientSide.onComplete>						
            $('loadingpager').hide();
        </prop:ClientSide.OnComplete>					
    </com:TActivePager>
    <img id="loadingpager" src="<%=$this->Page->Theme->baseUrl%>/design/ajax-loader-1.gif" style="display:none;" />    
</div>