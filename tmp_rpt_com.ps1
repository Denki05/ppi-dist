$ErrorActionPreference = 'Continue'
$progids = @('CrystalRuntime.Application','CrystalRuntime.Application.11')
foreach ($p in $progids) {
  try {
    $app = New-Object -ComObject $p
    [Console]::Out.WriteLine("COM OK: $p")
    $rpt = $app.OpenReport($args[0])
    [Console]::Out.WriteLine("opened: " + $rpt.Name)
    [Console]::Out.WriteLine("=== FORMULA FIELDS ===")
    $n = $rpt.FormulaFields.Count
    for ($i = 1; $i -le $n; $i++) {
      $f = $rpt.FormulaFields.Item($i)
      [Console]::Out.WriteLine("--- " + $f.Name)
      [Console]::Out.WriteLine([string]$f.Text)
    }
    break
  } catch {
    [Console]::Out.WriteLine("FAIL $p : " + $_.Exception.Message)
  }
}
