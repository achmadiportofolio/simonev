<com:NModalPanel ID="modalPrintOut" DefaultButton="btnPrintOut">
    <div style="width:700px;position: fixed;top: 90px;left: 400px;z-index: 2000;">        
        <table class="list">
            <thead>
                <tr>
                    <td colspan="2" class="left">Print Out <com:TActiveLabel ID="lblPrintout" /></td>
                </tr>
            </thead>
            <tbody>                
            <tr>
                <td class="left" width="150">
                    Output
                </td>
                <td class="left">
                    <com:TActiveDropDownList ID="cmbTipePrintOut" AutoPostBack="false">
                        <com:TListItem Value="excel2007" Text="Excel 2007 (*.xlsx)" />
                        <com:TListItem Value="excel2003" Text="Excel 2003 (*.xls)" />                            
                        <com:TListItem Value="pdf" Text="PDF (*.pdf)" />
                    </com:TActiveDropDownList>
                    <com:TActiveHyperLink ID="linkOutput" />
                </td>                                    
            </tr>
            <tr>
                <td class="left">
                    &nbsp;
                </td>
                <td class="left">
                    <com:TActiveButton ID="btnPrintOut" Text="Print" OnClick="Page.printOut" OnCallBack="Page.resetModalPrintOut" CssClass="input-submit">                    
                        <prop:ClientSide.OnPreDispatch>
                            $('loadingprintout').show(); 
                        </prop:ClientSide.OnPreDispatch>
                        <prop:ClientSide.OnLoading>
                            $('<%=$this->btnPrintOut->ClientId%>').disabled='disabled';						
                        </prop:ClientSide.OnLoading>
                        <prop:ClientSide.OnComplete>																	
                            $('<%=$this->btnPrintOut->ClientId%>').disabled='';						                            
                            $('loadingprintout').hide(); 
                        </prop:ClientSide.OnComplete>
                    </com:TActiveButton>
                    <com:TActiveButton ID="btnClosePrintOut" OnClick="Page.closePrintOutModal" Text="Close" ClientSide.PostState="false" CssClass="input-submit">
                         <prop:ClientSide.OnPreDispatch>
                            $('loadingprintout').show(); 
                        </prop:ClientSide.OnPreDispatch>
                        <prop:ClientSide.OnLoading>
                            $('<%=$this->btnClosePrintOut->ClientId%>').disabled='disabled';						
                        </prop:ClientSide.OnLoading>
                        <prop:ClientSide.OnComplete>																	
                            $('<%=$this->btnClosePrintOut->ClientId%>').disabled='';						                            
                            $('loadingprintout').hide(); 
                        </prop:ClientSide.OnComplete>
                    </com:TActiveButton>
                    <img id="loadingprintout" src="<%=$this->Page->Theme->baseUrl%>/design/ajax-loader-1.gif" style="display:none;" />
                </td>                                    
            </tr>
        </table>        
    </div>
</com:NModalPanel>