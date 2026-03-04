<script type="text/template" id="rowTemplate">
<tr>
    <td>
        <select name="items[__INDEX__][product_id]"
                class="form-select form-select-sm product-select w-100"
                required>
            <option value=""></option>
        </select>
    </td>

    <td class="text-center">
        <input type="number"
               name="items[__INDEX__][qty]"
               class="form-control form-control-sm text-end"
               min="0.1"
               step="0.01"
               required>
    </td>

    <td class="text-center">
        <button type="button"
                class="btn btn-sm btn-outline-danger removeRow">
            ×
        </button>
    </td>
</tr>
</script>