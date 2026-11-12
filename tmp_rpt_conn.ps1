$ErrorActionPreference = 'Continue'
$app = New-Object -ComObject 'CrystalRuntime.Application'
$rpt = $app.OpenReport($args[0])
$t = $rpt.Database.Tables.Item(1)
for ($i = 1; $i -le $rpt.Database.Tables.Count; $i++) {
  $t = $rpt.Database.Tables.Item($i)
  [Console]::Out.WriteLine("TABLE " + $i + ": " + $t.Name + " loc=" + $t.Location + " dll=" + $t.DllName)
}
try {
  $ci = $t.ConnectionInfo
  [Console]::Out.WriteLine("ConnInfo server=" + $ci.ServerName + " db=" + $ci.DatabaseName + " user=" + $ci.UserName + " type=" + $ci.ServerType)
} catch { [Console]::Out.WriteLine("no conninfo: " + $_.Exception.Message) }
[Console]::Out.WriteLine("--- Sections/objects text ---")
for ($s = 1; $s -le $rpt.Sections.Count; $s++) {
  $sec = $rpt.Sections.Item($s)
  [Console]::Out.WriteLine("SECTION " + $s + " " + $sec.Name)
  for ($o = 1; $o -le $sec.ReportObjects.Count; $o++) {
    $ob = $sec.ReportObjects.Item($o)
    $txt = ""
    try { $txt = [string]$ob.Text } catch {}
    if ($txt -ne "") { [Console]::Out.WriteLine("  [" + $ob.Name + "] " + ($txt -replace "`r`n", " \\n ")) }
  }
}
