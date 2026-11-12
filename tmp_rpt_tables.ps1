$ErrorActionPreference = 'Continue'
$app = New-Object -ComObject 'CrystalRuntime.Application'
$rpt = $app.OpenReport($args[0])
[Console]::Out.WriteLine("DB tables: " + $rpt.Database.Tables.Count)
for ($i = 1; $i -le $rpt.Database.Tables.Count; $i++) {
  $t = $rpt.Database.Tables.Item($i)
  [Console]::Out.WriteLine("TABLE " + $i + " name=" + $t.Name + " location=" + $t.Location)
  try {
    $f = $t.Fields
    for ($j = 1; $j -le $f.Count; $j++) {
      [Console]::Out.WriteLine("   field: " + $f.Item($j).Name + " | " + $f.Item($j).FormulaFieldName)
    }
  } catch { [Console]::Out.WriteLine("   (no fields: " + $_.Exception.Message + ")") }
}
[Console]::Out.WriteLine("=== PARAMS ===")
for ($i = 1; $i -le $rpt.ParameterFields.Count; $i++) {
  $p = $rpt.ParameterFields.Item($i)
  [Console]::Out.WriteLine($i.ToString() + ": " + $p.Name + " default=" + [string]$p.DefaultValue + " current=" + [string]$p.CurrentValue + " type=" + $p.ValueType)
}
[Console]::Out.WriteLine("=== GROUP/SORT ===")
[Console]::Out.WriteLine("RecordSelectionFormula: " + $rpt.RecordSelectionFormula)
