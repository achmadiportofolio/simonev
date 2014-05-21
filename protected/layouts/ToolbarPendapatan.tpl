<table width="100%" class="list">
    <tr>
        <td width="120" class="left">Tahun</td>
        <td class="left"><com:TActiveDropDownList ID="toolbarOptionsTahunAnggaran" OnSelectedIndexChanged="Page.changeTahunAnggaran">                    
                <prop:ClientSide.OnPreDispatch>
                    $('loadingbar').show();
                </prop:ClientSide.OnPreDispatch>
                <prop:ClientSide.onComplete>
                    $('loadingbar').hide();
                </prop:ClientSide.OnComplete>	
            </com:TActiveDropDownList>
        </td>
    </tr>    
    <tr>
        <td class="left">Bulan</td>
        <td class="left"><com:TActiveDropDownList ID="toolbarOptionsBulanRealisasi" OnSelectedIndexChanged="Page.changeBulanRealisasi">                    
                <prop:ClientSide.OnPreDispatch>
                    $('loadingbar').show();
                </prop:ClientSide.OnPreDispatch>
                <prop:ClientSide.onComplete>
                    $('loadingbar').hide();
                </prop:ClientSide.OnComplete>	
            </com:TActiveDropDownList>
        </td>
    </tr> 
</table>
