$ErrorActionPreference = 'Continue'
$asm = [System.Reflection.Assembly]::LoadWithPartialName('CrystalDecisions.CrystalReports.Engine')
[Console]::Out.WriteLine("ASM: " + $asm.FullName)
$Error.Clear()
try {
  $rpt = New-Object CrystalDecisions.CrystalReports.Engine.ReportDocument
  [Console]::Out.WriteLine("created")
  $rpt.Load($args[0])
  [Console]::Out.WriteLine("loaded")
  [Console]::Out.WriteLine("=== FORMULA FIELDS ===")
  foreach ($f in $rpt.DataDefinition.FormulaFields) {
      [Console]::Out.WriteLine("--- " + $f.Name)
      [Console]::Out.WriteLine([string]$f.Text)
  }
  [Console]::Out.WriteLine("=== SQL EXPRESSION FIELDS ===")
  foreach ($f in $rpt.DataDefinition.SQLExpressionFields) {
      [Console]::Out.WriteLine("--- " + $f.Name)
      [Console]::Out.WriteLine([string]$f.Text)
  }
  [Console]::Out.WriteLine("=== SUMMARY FIELDS ===")
  foreach ($f in $rpt.DataDefinition.SummaryFields) {
      [Console]::Out.WriteLine("--- " + $f.Name + " op=" + $f.Operation + " field=" + $f.Field.Name)
  }
  $rpt.Dispose()
} catch {
  $e = $_.Exception
  while ($e) { [Console]::Out.WriteLine("ERR: " + $e.GetType().FullName + " :: " + $e.Message); $e = $e.InnerException }
}
