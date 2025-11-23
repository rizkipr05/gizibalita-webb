# kp_smote.py  (di folder ml-api)
import os
import pandas as pd
import numpy as np

from sklearn.impute import SimpleImputer
from sklearn.preprocessing import MinMaxScaler
from imblearn.over_sampling import SMOTE
from sklearn.feature_selection import chi2
from sklearn.model_selection import train_test_split
from sklearn.tree import DecisionTreeClassifier

from flask import Flask, request, jsonify

# ==========================
# 1. TRAINING MODEL SEKALI
# ==========================

LABEL_MAP_REV = {
    0: 'Gizi Buruk',
    1: 'Gizi Kurang',
    2: 'Gizi Baik',
    3: 'Risiko Gizi Lebih',
    4: 'Gizi Lebih',
    5: 'Obesitas'
}

def load_and_train_model():
    # Path dataset relatif ke file ini
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    DATA_PATH = os.path.join(
        BASE_DIR,
        "Data Balita Puskesmas Depok 3 Sleman.xlsx - Application List.csv"
    )

    print("Load dataset dari:", DATA_PATH)
    df = pd.read_csv(DATA_PATH)

    # ---------- Imputasi ----------
    df_imputed = df.copy()
    num_cols = df_imputed.select_dtypes(include=[np.number]).columns
    cat_cols = df_imputed.select_dtypes(exclude=[np.number]).columns

    df_imputed[num_cols] = SimpleImputer(strategy='median').fit_transform(df_imputed[num_cols])
    df_imputed[cat_cols] = SimpleImputer(strategy='most_frequent').fit_transform(df_imputed[cat_cols])

    # ---------- Encoding ----------
    df_encoded = df_imputed.copy()

    df_encoded['Umur'] = df_encoded['Umur'].astype(str).str.extract(r'(\d+)').astype(float).astype('Int64')
    df_encoded['JK'] = df_encoded['JK'].astype(str).str.strip().map({'P': 0, 'L': 1})

    df_encoded['BB/U'] = df_encoded['BB/U'].astype(str).str.strip().map({
        'Sangat Kurang': 0, 'Kurang': 1, 'Normal': 2, 'Risiko Lebih': 3
    })
    df_encoded['TB/U'] = df_encoded['TB/U'].astype(str).str.strip().map({
        'Sangat Pendek': 0, 'Pendek': 1, 'Normal': 2
    })
    df_encoded['Status Gizi'] = df_encoded['Status Gizi'].astype(str).str.strip().map({
        'Gizi Buruk': 0,
        'Gizi Kurang': 1,
        'Gizi Baik': 2,
        'Risiko Gizi Lebih': 3,
        'Gizi Lebih': 4,
        'Obesitas': 5
    })

    # Desimal jadi float
    for col in ['LILA', 'ZS BB/U', 'ZS TB/U', 'ZS BB/TB']:
        if col in df_encoded.columns:
            df_encoded[col] = df_encoded[col].astype(str).str.replace(',', '.').astype(float)

    # ---------- Normalisasi ----------
    fitur_input = df_encoded.drop(columns=['Status Gizi'])
    scaler = MinMaxScaler()
    fitur_input_scaled = scaler.fit_transform(fitur_input)

    df_normalized = pd.DataFrame(fitur_input_scaled, columns=fitur_input.columns)
    df_normalized['Status Gizi'] = df_encoded['Status Gizi'].values

    # ---------- SMOTE ----------
    X = df_normalized.drop('Status Gizi', axis=1)
    y = df_normalized['Status Gizi']

    smote = SMOTE(random_state=42)
    X_resampled, y_resampled = smote.fit_resample(X, y)

    # ---------- Split ----------
    X_train, X_test, y_train, y_test = train_test_split(
        X_resampled,
        y_resampled,
        test_size=0.3,
        random_state=42,
        stratify=y_resampled
    )

    # ---------- Feature Selection ----------
    chi2_scores, p_values = chi2(X_train, y_train)
    chi2_results = pd.DataFrame({
        'Features': X_train.columns,
        'Chi2 Score': chi2_scores,
        'P-Value': p_values
    }).sort_values(by='Chi2 Score', ascending=False)

    selected_features = chi2_results[chi2_results['P-Value'] < 0.05]['Features'].tolist()
    if not selected_features:
        selected_features = list(X_train.columns)  # fallback

    X_train_selected = X_train[selected_features]

    # ---------- Decision Tree ----------
    model = DecisionTreeClassifier(
        random_state=42,
        class_weight='balanced',
        criterion='gini',
        max_depth=7,
        max_features='sqrt',
        min_samples_leaf=5,
        min_samples_split=10,
        ccp_alpha=0.01
    )
    model.fit(X_train_selected, y_train)

    print("Model selesai dilatih. Fitur terpilih:", selected_features)

    train_info = {
        "scaler": scaler,
        "selected_features": selected_features,
        "all_feature_names": list(fitur_input.columns),
    }

    return model, train_info


MODEL, TRAIN_INFO = load_and_train_model()

# ========================
# 2. FUNGSI PREDIKSI
# ========================

def predict_status_gizi(data_input: dict):
    """
    data_input contoh:
    {
      "Umur": "24",
      "JK": "L",
      "BB/U": "Normal",
      "TB/U": "Normal",
      "LILA": "13.5",
      "ZS BB/U": "-0.5",
      "ZS TB/U": "0.2",
      "ZS BB/TB": "-0.3"
    }
    """
    df_in = pd.DataFrame([data_input])

    # --- Encoding seperti training ---
    df_in['Umur'] = df_in['Umur'].astype(str).str.extract(r'(\d+)').astype(float)

    df_in['JK'] = df_in['JK'].astype(str).str.strip().map({'P': 0, 'L': 1})

    df_in['BB/U'] = df_in['BB/U'].astype(str).str.strip().map({
        'Sangat Kurang': 0, 'Kurang': 1, 'Normal': 2, 'Risiko Lebih': 3
    })
    df_in['TB/U'] = df_in['TB/U'].astype(str).str.strip().map({
        'Sangat Pendek': 0, 'Pendek': 1, 'Normal': 2
    })

    for col in ['LILA', 'ZS BB/U', 'ZS TB/U', 'ZS BB/TB']:
        if col in df_in.columns:
            df_in[col] = df_in[col].astype(str).str.replace(',', '.').astype(float)

    # Pastikan semua fitur ada
    all_features = TRAIN_INFO["all_feature_names"]
    for col in all_features:
        if col not in df_in.columns:
            df_in[col] = 0

    df_in = df_in[all_features]

    scaler = TRAIN_INFO["scaler"]
    X_scaled = scaler.transform(df_in)
    df_scaled = pd.DataFrame(X_scaled, columns=all_features)

    selected = TRAIN_INFO["selected_features"]
    X_sel = df_scaled[selected]

    y_pred = MODEL.predict(X_sel)[0]
    return int(y_pred), LABEL_MAP_REV.get(int(y_pred), "Unknown")

# ========================
# 3. FLASK API
# ========================

app = Flask(__name__)

@app.route("/")
def index():
    return jsonify({"message": "API Gizi Balita aktif"}), 200

@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"success": False, "error": "Body JSON kosong"}), 400

        class_id, class_label = predict_status_gizi(data)

        return jsonify({
            "success": True,
            "class_id": class_id,
            "class_label": class_label
        })
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500


if __name__ == "__main__":
    # jalankan:  python ml-api/kp_smote.py
    app.run(host="0.0.0.0", port=5000, debug=True)
