<com:NModalPanel ID="modalPrintOutRekapitulasi" DefaultButton="btnPrintOutRekapitulasi">
    <div style="width:700px;position: fixed;top: 90px;left: 400px;z-index: 2000;">        
        <table class="list">
            <thead>
                <tr>
                    <td colspan="2" class="left">Print Out <com:TActiveLabel ID="lblPrintoutRekapitulasi" /></td>
                </tr>
            </thead>
            <tbody>                
            <tr>
                <td class="left" width="150">
                    Output
                </td>
                <td class="left">
                    <com:TActiveDropDownList ID="cmbTipePrintOutRekapitulasi" AutoPostBack="false">
                        <com:TListItem Value="excel2007" Text="Excel 2007 (*.xlsx)" />
                        <com:TListItem Value="excel2003" Text="Excel 2003 (*.xls)" />                            
                        <com:TListItem Value="pdf" Text="PDF (*.pdf)" />
                    </com:TActiveDropDownList>
                    <com:TActiveHyperLink ID="linkOutputRekapitulasi" />
                </td>                                    
            </tr>
            <tr>
                <td class="left">
                    &nbsp;
                </td>
                <td class="left">
                    <com:TActiveButton ID="btnPrintOutRekapitulasi" Text="Print" OnClick="Page.printOutRekapitulasi" OnCallBack="Page.resetModalPrintOutRekapitulasi" CssClass="input-submit">                    
                        <prop:ClientSide.OnPreDispatch>
                            $('loadingprintoutrekapitulasi').show(); 
                        </prop:ClientSide.OnPreDispatch>
                        <prop:ClientSide.OnLoading>
                            $('<%=$this->btnPrintOutRekapitulasi->ClientId%>').disabled='disabled';						
                        </prop:ClientSide.OnLoading>
                        <prop:ClientSide.OnComplete>																	
                            $('<%=$this->btnPrintOutRekapitulasi->ClientId%>').disabled='';						                            
                            $('loadingprintoutrekapitulasi').hide(); 
                        </prop:ClientSide.OnComplete>
                    </com:TActiveButton>
                    <com:TActiveButton ID="btnClosePrintOutRekap" OnClick="Page.closePrintOutRekapitulasi" Text="Close" ClientSide.PostState="false" CssClass="input-submit">
                         <prop:ClientSide.OnPreDispatch>
                            $('loadingprintoutrekapitulasi').show(); 
                        </prop:ClientSide.OnPreDispatch>
                        <prop:ClientSide.OnLoading>
                            $('<%=$this->btnClosePrintOutRekap->ClientId%>').disabled='disabled';						
                        </prop:ClientSide.OnLoading>
                        <prop:ClientSide.OnComplete>																	
                            $('<%=$this->btnClosePrintOutRekap->ClientId%>').disabled='';						                            
                            $('loadingprintoutrekapitulasi').hide(); 
                        </prop:ClientSide.OnComplete>
                    </com:TActiveButton>
                    <img id="loadingprintoutrekapitulasi" src="<%=$this->Page->Theme->baseUrl%>/design/ajax-loader-1.gif" style="display:none;" />
                </td>                                    
            </tr>
        </table>        
    </div>
</com:NModalPanel>