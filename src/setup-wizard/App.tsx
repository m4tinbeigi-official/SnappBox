import React, { useState } from 'react';

const SetupWizard: React.FC = () => {
    const [step, setStep] = useState(1);

    return (
        <div className="sbqs-fullscreen flex items-center justify-center min-h-screen bg-slate-50 p-6">
            <div className="sbqs-container w-full max-w-2xl">
                {/* Elite Header */}
                <div className="flex justify-center mb-8">
                    <img src="/wp-content/plugins/snappbox/assets/img/sb-log.svg" className="h-12" alt="SnappBox" />
                </div>

                {/* Professional Stepper */}
                <div className="flex justify-between mb-8 px-4">
                    {[1, 2, 3, 4].map((s) => (
                        <div key={s} className="flex flex-col items-center flex-1">
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-500 
                ${step >= s ? 'bg-brand-green text-white shadow-lg' : 'bg-white text-slate-400 border border-slate-200'}`}>
                                {s}
                            </div>
                            <div className={`mt-2 text-xs font-semibold ${step === s ? 'text-brand-green' : 'text-slate-400'}`}>
                                {['API Token', 'Map Setup', 'Store Info', 'Finish'][s - 1]}
                            </div>
                        </div>
                    ))}
                </div>

                {/* Elite Card Content */}
                <div className="elite-card transform transition-all duration-700 hover:scale-[1.01]">
                    {step === 1 && (
                        <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4">
                            <h2 className="text-2xl font-bold text-slate-800">Welcome to SnappBox</h2>
                            <p className="text-slate-500">Enter your API token to connect your store with the SnappBox ecosystem.</p>

                            <div className="space-y-2">
                                <label className="text-sm font-semibold text-slate-700">API Key</label>
                                <div className="flex gap-2">
                                    <input
                                        type="text"
                                        className="flex-1 px-4 py-3 rounded-elite border border-slate-200 focus:ring-2 focus:ring-brand-green outline-none transition-all"
                                        placeholder="Paste your API key here..."
                                    />
                                    <a href="https://snapp-box.com/connect" target="_blank" className="elite-button whitespace-nowrap bg-brand-blue">
                                        Get Token
                                    </a>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Navigation Actions */}
                    <div className="flex justify-between mt-10">
                        {step > 1 && (
                            <button onClick={() => setStep(step - 1)} className="px-6 py-3 text-slate-500 font-medium hover:text-slate-800 transition-colors">
                                Back
                            </button>
                        )}
                        <button
                            onClick={() => step < 4 ? setStep(step + 1) : null}
                            className="elite-button ml-auto"
                        >
                            {step === 4 ? 'Complete Setup' : 'Save & Continue'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SetupWizard;
