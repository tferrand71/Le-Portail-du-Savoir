import { createClient } from '@supabase/supabase-js'

// REMPLACEZ PAR VOS VALEURS DU DASHBOARD SUPABASE
const supabaseUrl = 'https://lgltpmmfvrmujotjxjrn.supabase.co'
const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxnbHRwbW1mdnJtdWpvdGp4anJuIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ3MDI5NjEsImV4cCI6MjA4MDI3ODk2MX0.ZV6wjizFInCkSlbiO4i3TOekDwEQlTpNBMd4lzao1G0'

export const supabase = createClient(supabaseUrl, supabaseKey)