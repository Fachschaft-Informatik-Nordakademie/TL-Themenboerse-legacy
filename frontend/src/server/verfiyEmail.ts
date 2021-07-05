import axios from 'axios';

export type VerificationResult = {
  success: boolean;
  errorCode?: string;
};

export async function verifyEmail({ token }: { token: string }): Promise<VerificationResult> {
  try {
    await axios.post(`${process.env.NEXT_SERVER_SIDE_BACKEND_URL}/verify-email`, {
      token,
    });

    return {
      success: true,
    };
  } catch (e) {
    return {
      success: false,
      errorCode: e.response.data.code,
    };
  }
}
