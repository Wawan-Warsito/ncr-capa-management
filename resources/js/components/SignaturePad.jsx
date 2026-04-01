import React, { useEffect, useRef, useState } from 'react';

const SignaturePad = ({ onSave, height = 140 }) => {
    const canvasRef = useRef(null);
    const drawingRef = useRef(false);
    const lastPointRef = useRef({ x: 0, y: 0 });
    const [hasInk, setHasInk] = useState(false);

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;

        const ratio = window.devicePixelRatio || 1;
        const parent = canvas.parentElement;
        const width = parent ? parent.clientWidth : 500;

        canvas.width = Math.floor(width * ratio);
        canvas.height = Math.floor(height * ratio);
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;

        const ctx = canvas.getContext('2d');
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.lineWidth = 2;
        ctx.strokeStyle = '#111827';
    }, [height]);

    const getPoint = (e) => {
        const canvas = canvasRef.current;
        const rect = canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top,
        };
    };

    const start = (e) => {
        if (e.button !== undefined && e.button !== 0) return;
        const canvas = canvasRef.current;
        if (!canvas) return;
        canvas.setPointerCapture?.(e.pointerId);
        drawingRef.current = true;
        lastPointRef.current = getPoint(e);
    };

    const move = (e) => {
        if (!drawingRef.current) return;
        const canvas = canvasRef.current;
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const p = getPoint(e);
        const lp = lastPointRef.current;
        ctx.beginPath();
        ctx.moveTo(lp.x, lp.y);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        lastPointRef.current = p;
        setHasInk(true);
    };

    const end = (e) => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        try {
            canvas.releasePointerCapture?.(e.pointerId);
        } catch {
        }
        drawingRef.current = false;
    };

    const clear = () => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        
        // Reset canvas dimensions to clear it completely and reset state
        canvas.width = canvas.width; 
        
        // Re-apply context settings since resetting width clears them
        const ratio = window.devicePixelRatio || 1;
        const ctx = canvas.getContext('2d');
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.lineWidth = 2;
        ctx.strokeStyle = '#111827';
        
        setHasInk(false);
    };

    const save = () => {
        const canvas = canvasRef.current;
        if (!canvas || !hasInk) return;
        const dataUrl = canvas.toDataURL('image/png');
        onSave?.(dataUrl);
    };

    return (
        <div className="w-full">
            <div className="border border-gray-300 rounded-md bg-white">
                <canvas
                    ref={canvasRef}
                    onPointerDown={start}
                    onPointerMove={move}
                    onPointerUp={end}
                    onPointerCancel={end}
                    className="touch-none"
                />
            </div>
            <div className="mt-2 flex gap-2">
                <button type="button" onClick={clear} className="px-3 py-1 border rounded-md text-xs bg-white hover:bg-gray-50">
                    Clear
                </button>
                <button type="button" onClick={save} disabled={!hasInk} className="px-3 py-1 border rounded-md text-xs bg-blue-600 text-white disabled:opacity-50">
                    Save
                </button>
            </div>
        </div>
    );
};

export default SignaturePad;

