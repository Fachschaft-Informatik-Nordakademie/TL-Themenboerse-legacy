import { User } from '../../../src/types/user';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import {
  Button,
  Chip,
  Dialog,
  DialogActions,
  DialogContent,
  DialogContentText,
  DialogTitle,
  TextField,
  Tooltip,
  Typography,
} from '@material-ui/core';
import { useRouter } from 'next/router';
import React, { useEffect, useState } from 'react';
import { makeStyles } from '@material-ui/core/styles';
import { useTranslation } from 'next-i18next';
import Link from 'next/link';
import { PageComponent } from '../../../src/types/PageComponent';
import { Topic } from '../../../src/types/topic';
import axiosClient from '../../../src/api';

type Props = {
  user: User;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: { user, ...(await serverSideTranslations('de', ['topic'])) },
  };
}

const useStyles = makeStyles((theme) => ({
  section: {
    marginTop: theme.spacing(2),
  },
  tags: {
    marginLeft: theme.spacing(-1),
  },
  tagItem: { marginLeft: theme.spacing(1) },
  withdraw: {
    color: theme.palette.getContrastText(theme.palette.error.main),
    background: theme.palette.error.main,
  },
}));

function useForceUpdate(): () => void {
  const [, setValue] = useState(0); // integer state
  return () => setValue((value) => value + 1); // update the state to force render
}

const TopicDetail: PageComponent<Props> = ({ user }: Props): JSX.Element => {
  const { t: tTopic } = useTranslation('topic');
  const forceUpdate = useForceUpdate();
  const router = useRouter();
  const classes = useStyles();
  const topicId = router.query.id as string;

  const [topic, setTopic] = useState<Topic>();
  const [loading, setLoading] = useState<boolean>(true);
  const [open, setOpen] = useState<boolean>(false);
  const [content, setContent] = useState<string>('');
  const handleOpen = (): void => {
    setContent('');
    setOpen(true);
  };
  const handleClose = (): void => setOpen(false);
  const handleApplication = async (): Promise<void> => {
    try {
      await axiosClient.post('/application', { topic: topicId, content });
      topic.hasApplied = true;
      setTopic(topic);
      handleClose();
    } catch (e) {
      console.log(e); // TOOD: How do we handle error messages?
    }
  };
  const handleWithdraw = async (): Promise<void> => {
    try {
      await axiosClient.delete(`/application/${topicId}`);
      topic.hasApplied = false;
      setTopic(topic);
      forceUpdate();
    } catch (e) {
      console.log(e);
    }
  };

  const showApplyButton = (): boolean => {
    return topic.status === 'OPEN' && topic.author && user.id !== topic.author?.id;
  };

  const canApply = (): boolean => {
    return showApplyButton() && user.type === 'LDAP';
  };

  const fetchTopic = async (): Promise<void> => {
    setLoading(true);
    const response = await axiosClient.get('/topic/' + topicId);
    setTopic(response.data);
    setLoading(false);
  };

  useEffect(() => {
    fetchTopic();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (loading) {
    return <div>Wird geladen</div>;
  }

  const lengths = {
    application: 1000,
  };

  return (
    <div>
      <Typography gutterBottom variant="h4" component="h2">
        {tTopic('topic')} #{topicId}: {topic.title}
      </Typography>
      <div>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelDescription')}
        </Typography>
        <Typography variant="body1" component="p">
          {topic.description}
        </Typography>
      </div>

      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelRequirements')}
        </Typography>
        <Typography variant="body1" component="p">
          {topic.requirements}
        </Typography>
      </div>
      {topic.website && topic.website.length > 0 && (
        <div className={classes.section}>
          <Typography gutterBottom variant="h5" component="h3">
            {tTopic('labelWebsite')}
          </Typography>
          <Typography variant="body1" component="p">
            <a href={topic.website} target="_blank" rel="noopener noreferrer">
              {topic.website}
            </a>
          </Typography>
        </div>
      )}
      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelPages')}
        </Typography>
        <Typography variant="body1" component="p">
          {topic.pages}
        </Typography>
      </div>
      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelTags')}
        </Typography>
        <Typography variant="body1" component="p">
          <span className={classes.tags}>
            {topic.tags.map((t) => (
              <Chip classes={{ root: classes.tagItem }} key={t} label={t} />
            ))}
          </span>
        </Typography>
      </div>
      {showApplyButton() && !topic.hasApplied && (
        <>
          <Tooltip title={canApply() ? '' : tTopic('tooltipCisOnly')}>
            <span>
              <Button disabled={!canApply()} variant="contained" color="primary" onClick={handleOpen}>
                {tTopic('buttonApply')}
              </Button>
            </span>
          </Tooltip>
          <Dialog open={open} onClose={handleClose} aria-labelledby="form-dialog-title">
            <DialogTitle id="form-dialog-title">Bewerbung</DialogTitle>
            <DialogContent>
              <DialogContentText>{tTopic('applicationDialog')}</DialogContentText>
              <TextField
                margin="dense"
                id="name"
                label={tTopic('applicationContentLabel')}
                onChange={(e) => setContent(e.target.value)}
                type="text"
                fullWidth
                multiline
                rows={4}
                inputProps={{
                  maxLength: lengths.application,
                }}
                helperText={`${content.length}/${lengths.application}`}
              />
            </DialogContent>
            <DialogActions>
              <Button onClick={handleClose}>{tTopic('buttonCancel')}</Button>
              <Button onClick={handleApplication} variant="contained" color="primary">
                {tTopic('buttonApply')}
              </Button>
            </DialogActions>
          </Dialog>
        </>
      )}
      {showApplyButton() && topic.hasApplied && (
        <Button variant="contained" className={classes.withdraw} onClick={handleWithdraw}>
          {tTopic('buttonWithdraw')}
        </Button>
      )}
      {topic.author && user.id === topic.author.id && (
        <Link href={`/topic/${topicId}/edit`}>
          <Button variant="contained" color="primary" type="submit">
            {tTopic('buttonEdit')}
          </Button>
        </Link>
      )}
    </div>
  );
};

TopicDetail.layout = 'main';

export default TopicDetail;
