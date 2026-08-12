#!/usr/bin/env python3
import os
import sys
import json
import shutil
import argparse

def main():
    parser = argparse.ArgumentParser(description="Clean up unused locale JSON files")
    parser.add_argument("--action", choices=["list", "backup", "delete"], default="list", help="Action to perform")
    parser.add_argument("--backup-dir", default="/var/www/html/agsonhos/storage/locales_unused_backup", help="Target dir for backup")
    args = parser.parse_args()

    project_dir = "/var/www/html/agsonhos"
    locales_dir = os.path.join(project_dir, "Locales")
    report_file = "/home/kiruma/.gemini/antigravity-ide/brain/4ecf5dea-be82-414f-80dc-afb09885cd9d/scratch/detailed_locales_report.json"

    if not os.path.exists(report_file):
        print("Error: detailed_locales_report.json not found. Run categorize_locales.py first.")
        sys.exit(1)

    with open(report_file, "r") as f:
        report = json.load(f)

    unused_by_category = report.get("unused_by_category", {})
    all_unused_pt_br = []
    for cat_files in unused_by_category.values():
        all_unused_pt_br.extend(cat_files)

    languages = ["pt-br", "en-gb", "fr-fr"]
    files_to_process = []

    for pt_br_file in sorted(all_unused_pt_br):
        # Extract namespace suffix e.g., "account.edit.json" from "pt-br.account.edit.json"
        ns_suffix = pt_br_file[6:]
        for lang in languages:
            target_filename = f"{lang}.{ns_suffix}"
            lang_path = os.path.join(locales_dir, lang, target_filename)
            if os.path.exists(lang_path):
                files_to_process.append((lang, target_filename, lang_path))

    print(f"Total unused locale files found across all languages: {len(files_to_process)}")

    if args.action == "list":
        print("\n--- Files identified as unused ---")
        for lang, fname, fpath in files_to_process:
            print(f"  [{lang}] {fname}")
        print("\nTo move these files to a backup folder, run:")
        print("  python3 scratch/cleanup_locales.py --action backup")
        print("To delete these files permanently, run:")
        print("  python3 scratch/cleanup_locales.py --action delete")

    elif args.action == "backup":
        os.makedirs(args.backup_dir, exist_ok=True)
        count = 0
        for lang, fname, fpath in files_to_process:
            dest_dir = os.path.join(args.backup_dir, lang)
            os.makedirs(dest_dir, exist_ok=True)
            shutil.move(fpath, os.path.join(dest_dir, fname))
            count += 1
        print(f"\nSuccessfully moved {count} unused locale files to {args.backup_dir}")

    elif args.action == "delete":
        count = 0
        for lang, fname, fpath in files_to_process:
            os.remove(fpath)
            count += 1
        print(f"\nSuccessfully deleted {count} unused locale files.")

if __name__ == "__main__":
    main()
