import os

# Betiğin çalıştığı klasörü otomatik olarak ana dizin kabul et
base_dir = os.getcwd() 

# Markdown dosyasının güncel adı
md_file_path = os.path.join(base_dir, "flarum_ext_scout_v2_migration.md")

if not os.path.exists(md_file_path):
    print(f"❌ Hata: Markdown dosyası bulunamadı!\nAranan yol: {md_file_path}")
    exit()

with open(md_file_path, "r", encoding="utf-8") as f:
    lines = f.readlines()

current_file_path = None
file_content = []
is_reading_code = False

print("Dosyalar okunuyor ve senin dizinine uyarlanıyor...\n")

for line in lines:
    # Kod bloğu içindeki hedef dosya yolunu yakala
    if line.strip().startswith("=== DOSYA: "):
        raw_path = line.strip().replace("=== DOSYA: ", "").replace(" ===", "")
        
        # İçerikteki "C:\Users\sonsu\..." kısmını yoksayıp sadece eklenti içindeki göreceli yolu al
        if "flarum-ext-scout\\" in raw_path:
            relative_path = raw_path.split("flarum-ext-scout\\")[-1]
        elif "flarum-ext-scout/" in raw_path:
            relative_path = raw_path.split("flarum-ext-scout/")[-1]
        else:
            # Eşleşmezse sadece dosya adını al
            relative_path = os.path.basename(raw_path)
        
        # Senin bilgisayarındaki güncel çalışma diziniyle birleştir (Ufuk yoluna ayarlar)
        current_file_path = os.path.join(base_dir, relative_path)
        
        file_content = []
        is_reading_code = True
        continue
    
    if is_reading_code:
        # Kod bloğunun sonlandığını belirten ``` işaretini kontrol et
        if line.strip() == "```":
            is_reading_code = False
            
            if current_file_path:
                try:
                    # Hedef dizin mevcut değilse oluştur
                    os.makedirs(os.path.dirname(current_file_path), exist_ok=True)
                    
                    # Kodları dosyaya yaz
                    with open(current_file_path, "w", encoding="utf-8") as out_file:
                        out_file.writelines(file_content)
                    
                    print(f"✅ Başarılı: {relative_path}")
                except Exception as e:
                    print(f"❌ Yazma Hatası ({relative_path}): {e}")
            
            current_file_path = None
        else:
            # Formatı bozmadan içeriğe ekle
            file_content.append(line)

print("\n🚀 Tüm işlemler senin dizininde başarıyla tamamlandı.")